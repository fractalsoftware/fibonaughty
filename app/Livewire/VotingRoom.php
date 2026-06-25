<?php

namespace App\Livewire;

use App\Models\Room;
use App\Models\Participant;
use App\Models\Vote;
use App\Models\EstimationHistory;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Str;
use Livewire\Component;

class VotingRoom extends Component
{
    /**
     * Room identifier and state.
     */
    public string $roomId = '';
    public string $participantName = '';
    public string $taskTitleInput = '';
    public bool $showJoinModal = false;
    public ?string $selectedEstimate = null;
    public bool $isVoteLocked = false;
    public int $roundsCount = 1;

    /**
     * Serialized participant token to ensure AJAX and test request reliability.
     */
    public string $participantToken = '';

    /**
     * Active Participant Model instance (not serialized to public state).
     */
    protected ?Participant $participant = null;

    /**
     * Livewire Dynamic Event Listeners for real-time WebSocket Echo channel sync.
     */
    public function getListeners()
    {
        return [
            "echo:room.{$this->roomId},.RoomStateUpdated" => 'onRoomStateUpdated',
        ];
    }

    /**
     * Handle incoming real-time Room State updates.
     */
    public function onRoomStateUpdated(array $event)
    {
        $action = $event['action'] ?? 'update';

        // Fetch room and re-evaluate consensus for non-creator clients on reveal
        if ($action === 'reveal') {
            $room = Room::find($this->roomId);
            if ($room) {
                $activeParticipantIds = Participant::where('room_id', $this->roomId)
                    ->where('is_active', true)
                    ->pluck('id');

                $activeVotes = Vote::where('room_id', $this->roomId)
                    ->where('task_title', $room->current_task_title ?? 'Unnamed Task')
                    ->whereIn('participant_id', $activeParticipantIds)
                    ->get();

                if ($activeVotes->count() > 0) {
                    $uniqueValues = $activeVotes->pluck('estimate_value')->unique();
                    if ($uniqueValues->count() === 1) {
                        $this->dispatch('consensus-achieved');
                    }
                }
            }
        }
    }

    /**
     * Mount the component.
     */
    public function mount(string $id)
    {
        $this->roomId = $id;

        // Fetch room or fail
        $room = Room::findOrFail($id);

        // Check if the current user is the Room Creator (Scrum Master)
        $isCreator = (Auth::check() && Auth::id() === $room->creator_id);

        if ($isCreator) {
            $this->showJoinModal = false;
            $this->taskTitleInput = $room->current_task_title ?? '';
        } else {
            // Resolve Guest Participant from Cookie or property
            $token = $this->participantToken ?: request()->cookie('fibonaughty_guest_token');

            if ($token) {
                $participant = Participant::where('id', $token)
                    ->where('room_id', $this->roomId)
                    ->first();

                if ($participant) {
                    $this->participant = $participant;
                    $this->participantToken = $token;
                    $participant->update([
                        'is_active' => true,
                        'last_seen_at' => now(),
                    ]);
                    $this->showJoinModal = false;
                } else {
                    $this->showJoinModal = true;
                }
            } else {
                $this->showJoinModal = true;
            }
        }
    }

    /**
     * Action called by participants to register/join the room.
     */
    public function joinRoom()
    {
        $this->validate([
            'participantName' => 'required|string|min:2|max:30|regex:/^[a-zA-Z0-9\s_-]+$/',
        ]);

        // Clean and prepare the display name
        $name = strip_tags(trim($this->participantName));

        // Retrieve existing token, property, or generate a pristine UUID
        $token = $this->participantToken ?: (request()->cookie('fibonaughty_guest_token') ?: (string) Str::uuid());

        // Create or update the Participant database record
        $this->participant = Participant::updateOrCreate([
            'id' => $token,
            'room_id' => $this->roomId,
        ], [
            'name' => $name,
            'is_active' => true,
            'last_seen_at' => now(),
        ]);

        $this->participantToken = $token;
        $this->showJoinModal = false;

        // Queue guest token cookie securely (lasts for 30 days)
        Cookie::queue('fibonaughty_guest_token', $token, 43200);

        // Broadcast that a new participant has joined
        broadcast(new \App\Events\RoomStateUpdated($this->roomId, 'join'))->toOthers();
    }

    /**
     * Periodic ping action from the UI to track liveness and sync states.
     */
    public function ping()
    {
        $room = Room::find($this->roomId);
        if (!$room) {
            return;
        }

        // 1. Resolve current participant again using state token or cookie
        $token = $this->participantToken ?: request()->cookie('fibonaughty_guest_token');
        if ($token) {
            $this->participant = Participant::where('id', $token)
                ->where('room_id', $this->roomId)
                ->first();

            if ($this->participant) {
                $this->participant->update([
                    'last_seen_at' => now(),
                    'is_active' => true,
                ]);
                $this->participantToken = $token;
            }
        }

        // 2. Perform garbage collection of inactive participants (idle > 15 seconds)
        $deactivated = Participant::where('room_id', $this->roomId)
            ->where('last_seen_at', '<', now()->subSeconds(15))
            ->where('is_active', true)
            ->update(['is_active' => false]);

        if ($deactivated > 0) {
            broadcast(new \App\Events\RoomStateUpdated($this->roomId, 'leave'))->toOthers();
        }
    }

    /**
     * Cast or update a vote (Participant exclusive).
     */
    public function castVote(string $value)
    {
        $room = Room::findOrFail($this->roomId);

        if ($room->status !== 'voting') {
            session()->flash('error', __('messages.voting_closed'));
            return;
        }

        // Resolve active participant session using property first, then cookie fallback
        $token = $this->participantToken ?: request()->cookie('fibonaughty_guest_token');
        if (!$token) {
            session()->flash('error', __('messages.error_session_invalid'));
            return;
        }

        $this->participant = Participant::where('id', $token)
            ->where('room_id', $this->roomId)
            ->first();

        if (!$this->participant) {
            session()->flash('error', __('messages.error_session_invalid'));
            return;
        }

        // Check against allowed deck values
        $allowed = $room->deck_type === 'fibonacci'
            ? ['0', '1', '2', '3', '5', '8', '13', '20', '40', '100', '☕', '❓', '∞']
            : ['XS', 'S', 'M', 'L', 'XL', '☕', '❓'];

        if (!in_array($value, $allowed)) {
            session()->flash('error', __('messages.invalid_estimate'));
            return;
        }

        // Cast or override the vote
        Vote::updateOrCreate([
            'room_id' => $this->roomId,
            'participant_id' => $this->participant->id,
            'task_title' => $room->current_task_title ?? 'Unnamed Task',
        ], [
            'estimate_value' => $value,
        ]);

        $this->selectedEstimate = $value;
        $this->isVoteLocked = true;
        $this->participantToken = $token;

        broadcast(new \App\Events\RoomStateUpdated($this->roomId, 'vote'))->toOthers();
    }

    /**
     * Define or update the task (Creator exclusive).
     */
    public function updateTask()
    {
        $room = Room::findOrFail($this->roomId);
        if (Auth::id() !== $room->creator_id) {
            return;
        }

        $this->validate([
            'taskTitleInput' => 'required|string|min:3|max:150',
        ]);

        $room->update([
            'current_task_title' => strip_tags(trim($this->taskTitleInput)),
            'status' => 'idle',
        ]);

        $this->roundsCount = 1;

        // Clear existing votes for the old task
        Vote::where('room_id', $this->roomId)->delete();

        broadcast(new \App\Events\RoomStateUpdated($this->roomId, 'update'))->toOthers();
    }

    /**
     * Transition room status to voting (Creator exclusive).
     */
    public function startVoting()
    {
        $room = Room::findOrFail($this->roomId);
        if (Auth::id() !== $room->creator_id) {
            return;
        }

        if (empty($room->current_task_title)) {
            session()->flash('error', 'Please define a task before starting the voting process.');
            return;
        }

        $room->update([
            'status' => 'voting',
        ]);

        // Wipe votes so a clean voting cycle begins
        Vote::where('room_id', $this->roomId)->delete();
        $this->selectedEstimate = null;
        $this->isVoteLocked = false;

        broadcast(new \App\Events\RoomStateUpdated($this->roomId, 'start'))->toOthers();
    }

    /**
     * Reveal cards, transition status to revealed, and log history analytics (Creator exclusive).
     */
    public function revealCards()
    {
        $room = Room::findOrFail($this->roomId);
        if (Auth::id() !== $room->creator_id) {
            return;
        }

        $room->update([
            'status' => 'revealed',
        ]);

        // Calculate consensus statistics
        $activeParticipantIds = Participant::where('room_id', $this->roomId)
            ->where('is_active', true)
            ->pluck('id');

        $activeVotes = Vote::where('room_id', $this->roomId)
            ->where('task_title', $room->current_task_title ?? 'Unnamed Task')
            ->whereIn('participant_id', $activeParticipantIds)
            ->get();

        $consensusReached = false;
        $finalEstimate = null;

        if ($activeVotes->count() > 0) {
            $uniqueValues = $activeVotes->pluck('estimate_value')->unique();
            if ($uniqueValues->count() === 1) {
                $consensusReached = true;
                $finalEstimate = $uniqueValues->first();
            }
        }

        // Record or Update the Estimation History Log
        EstimationHistory::updateOrCreate([
            'room_id' => $this->roomId,
            'task_title' => $room->current_task_title ?? 'Unnamed Task',
        ], [
            'deck_type' => $room->deck_type,
            'final_estimate' => $finalEstimate,
            'consensus_reached' => $consensusReached,
            'rounds_count' => $this->roundsCount,
            'completed_at' => now(),
        ]);

        // Dispatch consensus achieved browser event for the Scrum Master
        if ($consensusReached) {
            $this->dispatch('consensus-achieved');
        }

        broadcast(new \App\Events\RoomStateUpdated($this->roomId, 'reveal'))->toOthers();
    }

    /**
     * Start another round / Revote on the same task (Creator exclusive).
     */
    public function resetRound()
    {
        $room = Room::findOrFail($this->roomId);
        if (Auth::id() !== $room->creator_id) {
            return;
        }

        $room->update([
            'status' => 'voting',
        ]);

        $this->roundsCount++;

        // Clear existing votes for clean revoting
        Vote::where('room_id', $this->roomId)->delete();
        $this->selectedEstimate = null;
        $this->isVoteLocked = false;

        broadcast(new \App\Events\RoomStateUpdated($this->roomId, 'reset'))->toOthers();
    }

    /**
     * Render the Livewire component.
     */
    public function render()
    {
        $room = Room::with('creator')->findOrFail($this->roomId);
        $isCreator = (Auth::check() && Auth::id() === $room->creator_id);

        // Fetch active participants in the room
        $activeParticipants = Participant::where('room_id', $this->roomId)
            ->where('is_active', true)
            ->orderBy('name', 'asc')
            ->get();

        // Fetch votes cast in the current round
        $votes = Vote::with('participant')
            ->where('room_id', $this->roomId)
            ->where('task_title', $room->current_task_title ?? 'Unnamed Task')
            ->get();

        // Check if current guest participant has voted
        if (!$isCreator) {
            $token = $this->participantToken ?: request()->cookie('fibonaughty_guest_token');
            if ($token && $room->current_task_title) {
                $currentVote = Vote::where('room_id', $this->roomId)
                    ->where('participant_id', $token)
                    ->where('task_title', $room->current_task_title)
                    ->first();

                if ($currentVote) {
                    $this->selectedEstimate = $currentVote->estimate_value;
                    $this->isVoteLocked = true;
                    $this->participantToken = $token;
                } else {
                    $this->selectedEstimate = null;
                    $this->isVoteLocked = false;
                }
            }
        }

        // Calculate consensus indicators for the view
        $consensusReached = false;
        $consensusValue = null;
        $averageEstimate = null;

        if ($room->status === 'revealed' && $votes->count() > 0) {
            $uniqueValues = $votes->pluck('estimate_value')->unique();
            if ($uniqueValues->count() === 1) {
                $consensusReached = true;
                $consensusValue = $uniqueValues->first();
            }

            // Calculate mathematical average for numeric votes
            $numericValues = [];
            foreach ($votes as $v) {
                $val = $v->estimate_value;
                if ($room->deck_type === 'fibonacci') {
                    if (is_numeric($val)) {
                        $numericValues[] = (float) $val;
                    }
                } else {
                    // T-Shirt values to numeric representation
                    $map = ['XS' => 1, 'S' => 2, 'M' => 3, 'L' => 4, 'XL' => 5];
                    if (array_key_exists($val, $map)) {
                        $numericValues[] = $map[$val];
                    }
                }
            }

            if (count($numericValues) > 0) {
                $avg = array_sum($numericValues) / count($numericValues);
                if ($room->deck_type === 'fibonacci') {
                    $averageEstimate = round($avg, 1);
                } else {
                    // Convert back to T-Shirt sizes for display
                    $reverseMap = [1 => 'XS', 2 => 'S', 3 => 'M', 4 => 'L', 5 => 'XL'];
                    $closest = round($avg);
                    $closest = max(1, min(5, $closest));
                    $averageEstimate = $reverseMap[$closest] . " (~" . round($avg, 1) . ")";
                }
            }
        }

        return view('livewire.voting-room', [
            'room' => $room,
            'isCreator' => $isCreator,
            'activeParticipants' => $activeParticipants,
            'votes' => $votes,
            'consensusReached' => $consensusReached,
            'consensusValue' => $consensusValue,
            'averageEstimate' => $averageEstimate,
        ])->layout('layouts.app');
    }
}
