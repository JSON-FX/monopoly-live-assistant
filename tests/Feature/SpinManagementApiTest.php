<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Session;
use App\Models\Spin;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Response;
use Tests\TestCase;

class SpinManagementApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_authenticated_user_can_add_spin_to_active_session(): void
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

    public function test_spin_creation_returns_updated_session_data(): void
    {
        $user = User::factory()->create();
        $session = Session::factory()->active()->create(['user_id' => $user->id]);

        $spinData = [
            'result' => '5',
            'bet_amount' => 2.00,
            'pl' => -2.00,
        ];

        $response = $this->actingAs($user, 'sanctum')
            ->postJson("/api/sessions/{$session->id}/spins", $spinData);

        $response->assertStatus(Response::HTTP_CREATED);
        $data = $response->json('data');

        // Verify updated session includes the new spin
        $this->assertCount(1, $data['spins']);
        $this->assertEquals('5', $data['spins'][0]['result']);
        $this->assertEquals(2.00, $data['spins'][0]['bet_amount']);
        $this->assertEquals(-2.00, $data['spins'][0]['pl']);

        // Verify P/L calculations are updated
        $this->assertArrayHasKey('pl_data', $data);
        $this->assertArrayHasKey('total_pl', $data['pl_data']);

        // Verify strategy recommendations are updated
        $this->assertArrayHasKey('strategy', $data);
        $this->assertArrayHasKey('next_action', $data['strategy']);
    }

    public function test_user_cannot_add_spin_to_another_users_session(): void
    {
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();
        $session = Session::factory()->active()->create(['user_id' => $user1->id]);

        $spinData = [
            'result' => '1',
            'bet_amount' => 1.00,
            'pl' => 35.00,
        ];

        $response = $this->actingAs($user2, 'sanctum')
            ->postJson("/api/sessions/{$session->id}/spins", $spinData);

        $response->assertStatus(Response::HTTP_FORBIDDEN)
            ->assertJson([
                'success' => false,
                'message' => 'Unauthorized access',
                'error' => 'You can only add spins to your own sessions.',
            ]);
    }

    public function test_cannot_add_spin_to_closed_session(): void
    {
        $user = User::factory()->create();
        $session = Session::factory()->completed()->create(['user_id' => $user->id]);

        $spinData = [
            'result' => '1',
            'bet_amount' => 1.00,
            'pl' => 35.00,
        ];

        $response = $this->actingAs($user, 'sanctum')
            ->postJson("/api/sessions/{$session->id}/spins", $spinData);

        $response->assertStatus(Response::HTTP_BAD_REQUEST)
            ->assertJson([
                'success' => false,
                'message' => 'Invalid data provided',
                'error' => 'Cannot add spins to a closed session.',
            ]);
    }

    public function test_spin_validation_requires_all_fields(): void
    {
        $user = User::factory()->create();
        $session = Session::factory()->active()->create(['user_id' => $user->id]);

        // Test missing result
        $response = $this->actingAs($user, 'sanctum')
            ->postJson("/api/sessions/{$session->id}/spins", [
                'bet_amount' => 1.00,
                'pl' => 35.00,
            ]);

        $response->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY)
            ->assertJsonValidationErrors(['result']);

        // Test missing bet_amount
        $response = $this->actingAs($user, 'sanctum')
            ->postJson("/api/sessions/{$session->id}/spins", [
                'result' => '1',
                'pl' => 35.00,
            ]);

        $response->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY)
            ->assertJsonValidationErrors(['bet_amount']);

        // Test missing pl
        $response = $this->actingAs($user, 'sanctum')
            ->postJson("/api/sessions/{$session->id}/spins", [
                'result' => '1',
                'bet_amount' => 1.00,
            ]);

        $response->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY)
            ->assertJsonValidationErrors(['pl']);
    }

    public function test_spin_validation_validates_data_types(): void
    {
        $user = User::factory()->create();
        $session = Session::factory()->active()->create(['user_id' => $user->id]);

        // Test invalid bet_amount (negative)
        $response = $this->actingAs($user, 'sanctum')
            ->postJson("/api/sessions/{$session->id}/spins", [
                'result' => '1',
                'bet_amount' => -1.00,
                'pl' => 35.00,
            ]);

        $response->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY)
            ->assertJsonValidationErrors(['bet_amount']);

        // Test invalid bet_amount (non-numeric)
        $response = $this->actingAs($user, 'sanctum')
            ->postJson("/api/sessions/{$session->id}/spins", [
                'result' => '1',
                'bet_amount' => 'invalid',
                'pl' => 35.00,
            ]);

        $response->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY)
            ->assertJsonValidationErrors(['bet_amount']);

        // Test invalid pl (non-numeric)
        $response = $this->actingAs($user, 'sanctum')
            ->postJson("/api/sessions/{$session->id}/spins", [
                'result' => '1',
                'bet_amount' => 1.00,
                'pl' => 'invalid',
            ]);

        $response->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY)
            ->assertJsonValidationErrors(['pl']);
    }

    public function test_spin_validation_validates_string_length(): void
    {
        $user = User::factory()->create();
        $session = Session::factory()->active()->create(['user_id' => $user->id]);

        // Test result too long
        $response = $this->actingAs($user, 'sanctum')
            ->postJson("/api/sessions/{$session->id}/spins", [
                'result' => str_repeat('a', 256),
                'bet_amount' => 1.00,
                'pl' => 35.00,
            ]);

        $response->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY)
            ->assertJsonValidationErrors(['result']);
    }

    public function test_unauthenticated_user_cannot_add_spin(): void
    {
        $response = $this->postJson('/api/sessions/1/spins', [
            'result' => '1',
            'bet_amount' => 1.00,
            'pl' => 35.00,
        ]);

        $response->assertStatus(Response::HTTP_UNAUTHORIZED);
    }

    public function test_adding_spin_to_nonexistent_session_returns_not_found(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user, 'sanctum')
            ->postJson('/api/sessions/999999/spins', [
                'result' => '1',
                'bet_amount' => 1.00,
                'pl' => 35.00,
            ]);

        $response->assertStatus(Response::HTTP_NOT_FOUND)
            ->assertJson([
                'success' => false,
                'message' => 'Session not found',
            ]);
    }

    public function test_multiple_spins_can_be_added_to_session(): void
    {
        $user = User::factory()->create();
        $session = Session::factory()->active()->create(['user_id' => $user->id]);

        // Add first spin
        $spin1Data = [
            'result' => '1',
            'bet_amount' => 1.00,
            'pl' => 35.00,
        ];

        $response1 = $this->actingAs($user, 'sanctum')
            ->postJson("/api/sessions/{$session->id}/spins", $spin1Data);

        $response1->assertStatus(Response::HTTP_CREATED);

        // Add second spin
        $spin2Data = [
            'result' => '5',
            'bet_amount' => 2.00,
            'pl' => -2.00,
        ];

        $response2 = $this->actingAs($user, 'sanctum')
            ->postJson("/api/sessions/{$session->id}/spins", $spin2Data);

        $response2->assertStatus(Response::HTTP_CREATED);

        // Verify both spins exist in database
        $this->assertDatabaseHas('spins', $spin1Data + ['session_id' => $session->id]);
        $this->assertDatabaseHas('spins', $spin2Data + ['session_id' => $session->id]);

        // Verify session now has 2 spins
        $sessionData = $response2->json('data');
        $this->assertCount(2, $sessionData['spins']);
    }

    public function test_spin_creation_validation_error_messages(): void
    {
        $user = User::factory()->create();
        $session = Session::factory()->active()->create(['user_id' => $user->id]);

        $response = $this->actingAs($user, 'sanctum')
            ->postJson("/api/sessions/{$session->id}/spins", []);

        $response->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY);
        $errors = $response->json('errors');

        $this->assertArrayHasKey('result', $errors);
        $this->assertArrayHasKey('bet_amount', $errors);
        $this->assertArrayHasKey('pl', $errors);

        $this->assertContains('The spin result is required.', $errors['result']);
        $this->assertContains('The bet amount is required.', $errors['bet_amount']);
        $this->assertContains('The profit/loss amount is required.', $errors['pl']);
    }
} 