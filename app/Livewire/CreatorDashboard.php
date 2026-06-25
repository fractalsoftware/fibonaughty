<?php

namespace App\Livewire;

use App\Models\Room;
use App\Models\EstimationHistory;
use App\Services\NanoIdGenerator;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class CreatorDashboard extends Component
{
    /**
     * Form state binding parameters.
     */
    public string $roomName = '';
    public string $deckType = 'fibonacci';

    /**
     * Form validation rules.
     */
    protected array $rules = [
        'roomName' => 'required|string|min:3|max:50|regex:/^[a-zA-Z0-9\s_-]+$/',
        'deckType' => 'required|string|in:fibonacci,tshirt',
    ];

    /**
     * Create a new planning poker room.
     *
     * @param NanoIdGenerator $generator
     * @return \Illuminate\Http\RedirectResponse|void
     */
    public function createNewSession(NanoIdGenerator $generator)
    {
        $this->validate();

        // Generate dynamic secure 12-character NanoID
        $nanoId = $generator->generate(12);

        // Ensure NanoID uniqueness (unlikely collision, but safe standard)
        while (Room::where('id', $nanoId)->exists()) {
            $nanoId = $generator->generate(12);
        }

        // Create the Room model record pointing to current user
        $room = Room::create([
            'id' => $nanoId,
            'creator_id' => Auth::id(),
            'name' => strip_tags(trim($this->roomName)),
            'deck_type' => $this->deckType,
            'status' => 'idle', // initial round state
        ]);

        // Clean form inputs
        $this->reset(['roomName', 'deckType']);

        // Success redirect to the dynamic livewire voting room
        return redirect()->route('room.show', ['id' => $nanoId]);
    }

    /**
     * Render the component view.
     */
    public function render()
    {
        // Query the creator's active rooms
        $activeRooms = Room::withCount(['participants' => function($query) {
            $query->where('is_active', true);
        }])
        ->where('creator_id', Auth::id())
        ->orderBy('created_at', 'desc')
        ->get();

        // Query the creator's past estimation logs
        $userRoomIds = $activeRooms->pluck('id')->toArray();
        $pastSessions = EstimationHistory::with('room')
            ->whereIn('room_id', $userRoomIds)
            ->orderBy('completed_at', 'desc')
            ->get();

        return view('livewire.creator-dashboard', [
            'activeRooms' => $activeRooms,
            'pastSessions' => $pastSessions,
        ])->layout('layouts.app');
    }
}
