<?php

namespace Tests\Feature;

use App\Models\Session;
use App\Models\Spin;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SessionRelationshipsTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_have_multiple_sessions(): void
    {
        $user = User::factory()->create();
        $sessions = Session::factory()->count(3)->create(['user_id' => $user->id]);

        $this->assertCount(3, $user->sessions);
        $sessions->each(function ($session) use ($user) {
            $this->assertEquals($user->id, $session->user_id);
        });
    }

    public function test_session_can_have_multiple_spins(): void
    {
        $session = Session::factory()->create();
        $spins = Spin::factory()->count(5)->create(['session_id' => $session->id]);

        $this->assertCount(5, $session->spins);
        $spins->each(function ($spin) use ($session) {
            $this->assertEquals($session->id, $spin->session_id);
        });
    }

    public function test_deleting_user_cascades_to_sessions_and_spins(): void
    {
        $user = User::factory()->create();
        $session = Session::factory()->create(['user_id' => $user->id]);
        $spins = Spin::factory()->count(3)->create(['session_id' => $session->id]);

        $this->assertDatabaseHas('users', ['id' => $user->id]);
        $this->assertDatabaseHas('game_sessions', ['id' => $session->id]);
        $this->assertDatabaseCount('spins', 3);

        $user->delete();

        $this->assertDatabaseMissing('users', ['id' => $user->id]);
        $this->assertDatabaseMissing('game_sessions', ['id' => $session->id]);
        $this->assertDatabaseCount('spins', 0);
    }

    public function test_deleting_session_cascades_to_spins(): void
    {
        $session = Session::factory()->create();
        $spins = Spin::factory()->count(3)->create(['session_id' => $session->id]);

        $this->assertDatabaseHas('game_sessions', ['id' => $session->id]);
        $this->assertDatabaseCount('spins', 3);

        $session->delete();

        $this->assertDatabaseMissing('game_sessions', ['id' => $session->id]);
        $this->assertDatabaseCount('spins', 0);
    }

    public function test_session_and_spin_factories_work_together(): void
    {
        $session = Session::factory()
            ->has(Spin::factory()->count(3))
            ->create();

        $this->assertCount(3, $session->spins);
        $session->spins->each(function ($spin) use ($session) {
            $this->assertEquals($session->id, $spin->session_id);
            $this->assertInstanceOf(Session::class, $spin->session);
        });
    }

    public function test_complete_session_with_spins_workflow(): void
    {
        // Create user with an active session
        $user = User::factory()->create();
        $session = Session::factory()->active()->create(['user_id' => $user->id]);
        
        // Add some spins to the session
        $spins = Spin::factory()->count(5)->create(['session_id' => $session->id]);
        
        // Verify the complete relationship chain
        $this->assertEquals($user->id, $session->user->id);
        $this->assertCount(5, $session->spins);
        $this->assertCount(1, $user->sessions);
        
        // Each spin should belong to the session
        $spins->each(function ($spin) use ($session) {
            $this->assertEquals($session->id, $spin->session->id);
        });
    }

    public function test_session_timestamps_are_maintained(): void
    {
        $session = Session::factory()->create();
        
        $this->assertNotNull($session->created_at);
        $this->assertNotNull($session->updated_at);
        
        // Update session and verify timestamp changes
        $originalUpdatedAt = $session->updated_at;
        sleep(1); // Ensure timestamp difference
        
        $session->update(['end_time' => now()]);
        
        $this->assertGreaterThan($originalUpdatedAt, $session->fresh()->updated_at);
    }

    public function test_spin_timestamps_are_maintained(): void
    {
        $spin = Spin::factory()->create();
        
        $this->assertNotNull($spin->created_at);
        $this->assertNotNull($spin->updated_at);
        
        // Update spin and verify timestamp changes
        $originalUpdatedAt = $spin->updated_at;
        sleep(1); // Ensure timestamp difference
        
        $spin->update(['pl' => 50.00]);
        
        $this->assertGreaterThan($originalUpdatedAt, $spin->fresh()->updated_at);
    }

    public function test_database_constraints_prevent_orphaned_records(): void
    {
        $this->expectException(\Illuminate\Database\QueryException::class);
        
        // Try to create a spin with non-existent session_id
        Spin::create([
            'session_id' => 99999,
            'result' => '1',
            'bet_amount' => 10.00,
            'pl' => -10.00,
        ]);
    }

    public function test_session_foreign_key_constraint(): void
    {
        $this->expectException(\Illuminate\Database\QueryException::class);
        
        // Try to create a session with non-existent user_id
        Session::create([
            'user_id' => 99999,
            'start_time' => now(),
            'end_time' => null,
        ]);
    }
} 