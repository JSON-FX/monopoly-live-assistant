<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Session;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Response;
use Tests\TestCase;

class SessionManagementApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_authenticated_user_can_retrieve_session_details(): void
    {
        $user = User::factory()->create();
        $session = Session::factory()->active()->create(['user_id' => $user->id]);

        $response = $this->actingAs($user, 'sanctum')
            ->getJson("/api/sessions/{$session->id}");

        $response->assertStatus(Response::HTTP_OK)
            ->assertJson([
                'success' => true,
                'message' => 'Session retrieved successfully',
            ])
            ->assertJsonStructure([
                'success',
                'message',
                'data' => [
                    'id',
                    'user_id',
                    'start_time',
                    'end_time',
                    'created_at',
                    'updated_at',
                    'user' => ['id', 'name', 'email'],
                    'pl_data' => [
                        'total_pl',
                        'running_totals',
                        'statistics',
                    ],
                    'strategy' => [
                        'next_action',
                        'configuration',
                    ],
                    'spins',
                ],
            ]);

        $sessionData = $response->json('data');
        $this->assertEquals($session->id, $sessionData['id']);
        $this->assertEquals($user->id, $sessionData['user_id']);
    }

    public function test_session_details_include_pl_calculations_and_strategy(): void
    {
        $user = User::factory()->create();
        $session = Session::factory()->active()->create(['user_id' => $user->id]);

        $response = $this->actingAs($user, 'sanctum')
            ->getJson("/api/sessions/{$session->id}");

        $response->assertStatus(Response::HTTP_OK);
        $data = $response->json('data');

        // Verify P/L data structure
        $this->assertArrayHasKey('pl_data', $data);
        $this->assertArrayHasKey('total_pl', $data['pl_data']);
        $this->assertArrayHasKey('running_totals', $data['pl_data']);
        $this->assertArrayHasKey('statistics', $data['pl_data']);

        // Verify strategy data structure
        $this->assertArrayHasKey('strategy', $data);
        $this->assertArrayHasKey('next_action', $data['strategy']);
        $this->assertArrayHasKey('configuration', $data['strategy']);
    }

    public function test_user_cannot_access_another_users_session(): void
    {
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();
        $session = Session::factory()->active()->create(['user_id' => $user1->id]);

        $response = $this->actingAs($user2, 'sanctum')
            ->getJson("/api/sessions/{$session->id}");

        $response->assertStatus(Response::HTTP_FORBIDDEN)
            ->assertJson([
                'success' => false,
                'message' => 'Unauthorized access',
                'error' => 'You can only access your own sessions.',
            ]);
    }

    public function test_session_not_found_returns_proper_error(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user, 'sanctum')
            ->getJson('/api/sessions/999999');

        $response->assertStatus(Response::HTTP_NOT_FOUND)
            ->assertJson([
                'success' => false,
                'message' => 'Session not found',
            ]);
    }

    public function test_unauthenticated_user_cannot_access_session(): void
    {
        $response = $this->getJson('/api/sessions/1');
        $response->assertStatus(Response::HTTP_UNAUTHORIZED);
    }

    public function test_authenticated_user_can_close_active_session(): void
    {
        $user = User::factory()->create();
        $session = Session::factory()->active()->create(['user_id' => $user->id]);

        $response = $this->actingAs($user, 'sanctum')
            ->putJson("/api/sessions/{$session->id}/close");

        $response->assertStatus(Response::HTTP_OK)
            ->assertJson([
                'success' => true,
                'message' => 'Session closed successfully',
            ])
            ->assertJsonStructure([
                'success',
                'message',
                'data' => [
                    'id',
                    'end_time',
                    'pl_data',
                    'strategy',
                ],
            ]);

        // Verify session was closed in database
        $session->refresh();
        $this->assertNotNull($session->end_time);
    }

    public function test_cannot_close_already_closed_session(): void
    {
        $user = User::factory()->create();
        $session = Session::factory()->completed()->create(['user_id' => $user->id]);

        $response = $this->actingAs($user, 'sanctum')
            ->putJson("/api/sessions/{$session->id}/close");

        $response->assertStatus(Response::HTTP_BAD_REQUEST)
            ->assertJson([
                'success' => false,
                'message' => 'Invalid operation',
                'error' => 'Session is already closed.',
            ]);
    }

    public function test_user_cannot_close_another_users_session(): void
    {
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();
        $session = Session::factory()->active()->create(['user_id' => $user1->id]);

        $response = $this->actingAs($user2, 'sanctum')
            ->putJson("/api/sessions/{$session->id}/close");

        $response->assertStatus(Response::HTTP_FORBIDDEN)
            ->assertJson([
                'success' => false,
                'message' => 'Unauthorized access',
                'error' => 'You can only close your own sessions.',
            ]);
    }

    public function test_unauthenticated_user_cannot_close_session(): void
    {
        $response = $this->putJson('/api/sessions/1/close');
        $response->assertStatus(Response::HTTP_UNAUTHORIZED);
    }

    public function test_closing_nonexistent_session_returns_not_found(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user, 'sanctum')
            ->putJson('/api/sessions/999999/close');

        $response->assertStatus(Response::HTTP_NOT_FOUND)
            ->assertJson([
                'success' => false,
                'message' => 'Session not found',
            ]);
    }

    public function test_session_closing_returns_final_statistics(): void
    {
        $user = User::factory()->create();
        $session = Session::factory()->active()->create(['user_id' => $user->id]);

        $response = $this->actingAs($user, 'sanctum')
            ->putJson("/api/sessions/{$session->id}/close");

        $response->assertStatus(Response::HTTP_OK);
        $data = $response->json('data');

        // Verify final statistics are included
        $this->assertArrayHasKey('pl_data', $data);
        $this->assertArrayHasKey('statistics', $data['pl_data']);
        $this->assertNotNull($data['end_time']);
    }
} 