<?php

namespace Tests\Feature;

use App\Models\Room;
use App\Models\User;
use App\Models\Participant;
use App\Models\Vote;
use App\Models\EstimationHistory;
use App\Livewire\VotingRoom;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class VotingRoomTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test that guest participants can view the room, enter their nickname,
     * and successfully register themselves.
     */
    public function test_guest_can_access_room_and_join_with_nickname()
    {
        $creator = User::factory()->create();

        $room = Room::create([
            'id' => 'abc-123-xyz',
            'creator_id' => $creator->id,
            'name' => 'Sprint Planning 1',
            'deck_type' => 'fibonacci',
            'status' => 'idle',
        ]);

        // Start Livewire test as guest
        Livewire::test(VotingRoom::class, ['id' => $room->id])
            ->assertSet('showJoinModal', true)
            ->set('participantName', 'CodeNinja')
            ->call('joinRoom')
            ->assertSet('showJoinModal', false)
            ->assertHasNoErrors();

        // Verify database records
        $this->assertDatabaseHas('participants', [
            'room_id' => $room->id,
            'name' => 'CodeNinja',
            'is_active' => true,
        ]);
    }

    /**
     * Test that only the room creator can update task titles and toggle room statuses.
     */
    public function test_creator_can_update_task_and_control_states()
    {
        $creator = User::factory()->create();

        $room = Room::create([
            'id' => 'def-456-uvw',
            'creator_id' => $creator->id,
            'name' => 'Sprint Planning 2',
            'deck_type' => 'tshirt',
            'status' => 'idle',
        ]);

        // Authenticate as the creator
        $this->actingAs($creator);

        Livewire::test(VotingRoom::class, ['id' => $room->id])
            ->assertSet('showJoinModal', false)
            ->set('taskTitleInput', 'Build Glassmorphic Login')
            ->call('updateTask')
            ->assertSet('roundsCount', 1)
            ->call('startVoting');

        // Verify room status in DB has changed to voting
        $this->assertDatabaseHas('rooms', [
            'id' => $room->id,
            'current_task_title' => 'Build Glassmorphic Login',
            'status' => 'voting',
        ]);
    }

    /**
     * Test that participants can cast estimates, and revealing those estimates calculates consensus correctly.
     */
    public function test_participant_can_cast_vote_and_reveal_consensus()
    {
        $creator = User::factory()->create();

        $room = Room::create([
            'id' => 'ghi-789-rst',
            'creator_id' => $creator->id,
            'name' => 'Sprint Planning 3',
            'deck_type' => 'fibonacci',
            'status' => 'voting',
            'current_task_title' => 'Setup Reverb WebSockets',
        ]);

        // Create a participant
        $participantToken = 'test-token-uuid-1234';
        $participant = Participant::create([
            'id' => $participantToken,
            'room_id' => $room->id,
            'name' => 'BugSlayer',
            'is_active' => true,
            'last_seen_at' => now(),
        ]);

        // Mock the guest cookie for the request
        $this->withCookie('fibonaughty_guest_token', $participantToken);

        // Cast vote as participant
        Livewire::test(VotingRoom::class, ['id' => $room->id])
            ->set('participantToken', $participantToken)
            ->call('castVote', '8')
            ->assertSet('selectedEstimate', '8')
            ->assertSet('isVoteLocked', true);

        // Verify vote recorded in DB
        $this->assertDatabaseHas('votes', [
            'room_id' => $room->id,
            'participant_id' => $participant->id,
            'estimate_value' => '8',
        ]);

        // Now log in as the creator and reveal cards
        $this->actingAs($creator);

        Livewire::test(VotingRoom::class, ['id' => $room->id])
            ->call('revealCards');

        // Verify room status updated in DB
        $this->assertDatabaseHas('rooms', [
            'id' => $room->id,
            'status' => 'revealed',
        ]);

        // Since BugSlayer was the only voter and voted '8', consensus was achieved!
        $this->assertDatabaseHas('estimation_history', [
            'room_id' => $room->id,
            'task_title' => 'Setup Reverb WebSockets',
            'consensus_reached' => true,
            'final_estimate' => '8',
        ]);
    }

    /**
     * Test that Room Actions correctly dispatch the RoomStateUpdated broadcast event.
     */
    public function test_room_actions_broadcast_state_updates()
    {
        \Illuminate\Support\Facades\Event::fake();

        $creator = User::factory()->create();

        $room = Room::create([
            'id' => 'xyz-987-abc',
            'creator_id' => $creator->id,
            'name' => 'Real-Time Sync Test',
            'deck_type' => 'fibonacci',
            'status' => 'idle',
        ]);

        // 1. Join room should dispatch RoomStateUpdated
        Livewire::test(VotingRoom::class, ['id' => $room->id])
            ->set('participantName', 'WebsocketWizard')
            ->call('joinRoom');

        \Illuminate\Support\Facades\Event::assertDispatched(\App\Events\RoomStateUpdated::class, function ($event) use ($room) {
            return $event->roomId === $room->id && $event->action === 'join';
        });

        // 2. Cast vote should dispatch RoomStateUpdated
        $participantToken = 'test-token-uuid-abc';
        Participant::create([
            'id' => $participantToken,
            'room_id' => $room->id,
            'name' => 'WebsocketWizard',
            'is_active' => true,
            'last_seen_at' => now(),
        ]);

        // Put room into voting status so castVote succeeds
        $room->update(['status' => 'voting', 'current_task_title' => 'Implement Echo']);

        Livewire::test(VotingRoom::class, ['id' => $room->id])
            ->set('participantToken', $participantToken)
            ->call('castVote', '5');

        \Illuminate\Support\Facades\Event::assertDispatched(\App\Events\RoomStateUpdated::class, function ($event) use ($room) {
            return $event->roomId === $room->id && $event->action === 'vote';
        });
    }
}
