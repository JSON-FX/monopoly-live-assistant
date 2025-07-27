<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Session;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Response;
use Tests\TestCase;

class SessionCreationTest extends TestCase
{
    use RefreshDatabase;

    public function test_authenticated_user_can_create_session(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user, 'sanctum')
            ->postJson('/api/sessions');

        $response->assertStatus(Response::HTTP_CREATED)
            ->assertJson([
                'success' => true,
                'message' => 'Session created successfully',
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
                    'user' => [
                        'id',
                        'name',
                        'email',
                    ],
                ],
            ]);

        $this->assertDatabaseHas('game_sessions', [
            'user_id' => $user->id,
            'end_time' => null,
        ]);

        $sessionData = $response->json('data');
        $this->assertEquals($user->id, $sessionData['user_id']);
        $this->assertNull($sessionData['end_time']);
        $this->assertEquals($user->name, $sessionData['user']['name']);
    }

    public function test_authenticated_user_can_retrieve_session_details(): void
    {
        $user = User::factory()->create();
        $session = Session::factory()->create(['user_id' => $user->id]);

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
                    'user' => [
                        'id',
                        'name',
                        'email',
                    ],
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
    }

    public function test_authenticated_user_can_add_spin_to_session(): void
    {
        $user = User::factory()->create();
        $session = Session::factory()->active()->create(['user_id' => $user->id]);

        $spinData = [
            'result' => '1',
            'bet_amount' => 1.00,
            'pl' => 35.00,
        ];

        $response = $this->actingAs($user, 'sanctum')
            ->postJson("/api/sessions/{$session->id}/spins", $spinData);

        $response->assertStatus(Response::HTTP_CREATED)
            ->assertJson([
                'success' => true,
                'message' => 'Spin added successfully',
            ])
            ->assertJsonStructure([
                'success',
                'message',
                'data' => [
                    'id',
                    'spins',
                    'pl_data',
                    'strategy',
                ],
            ]);

        $this->assertDatabaseHas('spins', [
            'session_id' => $session->id,
            'result' => '1',
            'bet_amount' => 1.00,
            'pl' => 35.00,
        ]);
    }

    public function test_authenticated_user_can_close_session(): void
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

    public function test_all_session_endpoints_require_authentication(): void
    {
        // Test session show endpoint
        $response = $this->getJson('/api/sessions/1');
        $response->assertStatus(Response::HTTP_UNAUTHORIZED);

        // Test session close endpoint
        $response = $this->putJson('/api/sessions/1/close');
        $response->assertStatus(Response::HTTP_UNAUTHORIZED);

        // Test spin creation endpoint
        $response = $this->postJson('/api/sessions/1/spins', [
            'result' => '1',
            'bet_amount' => 1.00,
            'pl' => 35.00,
        ]);
        $response->assertStatus(Response::HTTP_UNAUTHORIZED);
    }

    public function test_user_cannot_access_another_users_session(): void
    {
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();
        $session = Session::factory()->create(['user_id' => $user1->id]);

        $response = $this->actingAs($user2, 'sanctum')
            ->getJson("/api/sessions/{$session->id}");

        $response->assertStatus(Response::HTTP_FORBIDDEN)
            ->assertJson([
                'success' => false,
                'message' => 'Unauthorized access',
            ]);
    }

    public function test_unauthenticated_user_cannot_create_session(): void
    {
        $response = $this->postJson('/api/sessions');

        $response->assertStatus(Response::HTTP_UNAUTHORIZED);
    }

    public function test_session_creation_includes_proper_timestamps(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user, 'sanctum')
            ->postJson('/api/sessions');

        $response->assertStatus(Response::HTTP_CREATED);

        $sessionData = $response->json('data');
        $this->assertNotNull($sessionData['start_time']);
        $this->assertNotNull($sessionData['created_at']);
        $this->assertNotNull($sessionData['updated_at']);
        $this->assertNull($sessionData['end_time']);

        // Verify timestamp format (ISO 8601 with microseconds)
        $this->assertMatchesRegularExpression(
            '/^\d{4}-\d{2}-\d{2}T\d{2}:\d{2}:\d{2}\.\d{6}Z$/',
            $sessionData['start_time']
        );
    }

    public function test_session_creation_associates_with_authenticated_user(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user, 'sanctum')
            ->postJson('/api/sessions');

        $response->assertStatus(Response::HTTP_CREATED);

        $session = Session::where('user_id', $user->id)->first();
        $this->assertNotNull($session);
        $this->assertEquals($user->id, $session->user_id);
        $this->assertNotNull($session->start_time);
        $this->assertNull($session->end_time);
    }

    public function test_session_creation_response_format(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user, 'sanctum')
            ->postJson('/api/sessions');

        $response->assertStatus(Response::HTTP_CREATED)
            ->assertHeader('Content-Type', 'application/json')
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
                    'user' => [
                        'id',
                        'name',
                        'email',
                    ],
                ],
            ]);

        $responseData = $response->json();
        $this->assertTrue($responseData['success']);
        $this->assertIsString($responseData['message']);
        $this->assertIsArray($responseData['data']);
    }

    public function test_multiple_sessions_can_be_created_for_same_user(): void
    {
        $user = User::factory()->create();

        // Create first session
        $response1 = $this->actingAs($user, 'sanctum')
            ->postJson('/api/sessions');
        $response1->assertStatus(Response::HTTP_CREATED);

        // Create second session
        $response2 = $this->actingAs($user, 'sanctum')
            ->postJson('/api/sessions');
        $response2->assertStatus(Response::HTTP_CREATED);

        // Verify both sessions exist
        $this->assertEquals(2, Session::where('user_id', $user->id)->count());

        // Verify they have different IDs
        $session1Data = $response1->json('data');
        $session2Data = $response2->json('data');
        $this->assertNotEquals($session1Data['id'], $session2Data['id']);
    }

    public function test_session_creation_handles_database_transaction(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user, 'sanctum')
            ->postJson('/api/sessions');

        $response->assertStatus(Response::HTTP_CREATED);

        // Verify session was created atomically
        $session = Session::where('user_id', $user->id)->first();
        $this->assertNotNull($session);
        $this->assertNotNull($session->user); // Verify relationship is loaded
    }

    public function test_api_user_endpoint_works_with_sanctum(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user, 'sanctum')
            ->getJson('/api/user');

        $response->assertStatus(Response::HTTP_OK)
            ->assertJson([
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
            ]);
    }

    public function test_api_user_endpoint_requires_authentication(): void
    {
        $response = $this->getJson('/api/user');

        $response->assertStatus(Response::HTTP_UNAUTHORIZED);
    }
} 