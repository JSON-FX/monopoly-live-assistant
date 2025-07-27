<?php

namespace Tests\Unit;

use App\Models\Session;
use App\Models\Spin;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SessionTest extends TestCase
{
    use RefreshDatabase;

    public function test_session_can_be_created_with_valid_attributes(): void
    {
        $user = User::factory()->create();
        $startTime = now();
        
        $session = Session::create([
            'user_id' => $user->id,
            'start_time' => $startTime,
            'end_time' => null,
        ]);

        $this->assertInstanceOf(Session::class, $session);
        $this->assertEquals($user->id, $session->user_id);
        $this->assertEquals($startTime->format('Y-m-d H:i:s'), $session->start_time->format('Y-m-d H:i:s'));
        $this->assertNull($session->end_time);
    }

    public function test_session_belongs_to_user(): void
    {
        $user = User::factory()->create();
        $session = Session::factory()->create(['user_id' => $user->id]);

        $this->assertInstanceOf(User::class, $session->user);
        $this->assertEquals($user->id, $session->user->id);
    }

    public function test_session_has_many_spins(): void
    {
        $session = Session::factory()->create();
        $spins = Spin::factory()->count(3)->create(['session_id' => $session->id]);

        $this->assertCount(3, $session->spins);
        $this->assertInstanceOf(Spin::class, $session->spins->first());
    }

    public function test_session_casts_timestamps_correctly(): void
    {
        $session = Session::factory()->create([
            'start_time' => '2025-01-01 10:00:00',
            'end_time' => '2025-01-01 11:00:00',
        ]);

        $this->assertInstanceOf(\Illuminate\Support\Carbon::class, $session->start_time);
        $this->assertInstanceOf(\Illuminate\Support\Carbon::class, $session->end_time);
    }

    public function test_session_validation_rules_are_correct(): void
    {
        $rules = Session::validationRules();

        $this->assertArrayHasKey('user_id', $rules);
        $this->assertArrayHasKey('start_time', $rules);
        $this->assertArrayHasKey('end_time', $rules);
        
        $this->assertStringContainsString('required', $rules['user_id']);
        $this->assertStringContainsString('exists:users,id', $rules['user_id']);
        $this->assertStringContainsString('required', $rules['start_time']);
        $this->assertStringContainsString('nullable', $rules['end_time']);
        $this->assertStringContainsString('after:start_time', $rules['end_time']);
    }

    public function test_session_fillable_attributes(): void
    {
        $session = new Session();
        
        $this->assertEquals(
            ['user_id', 'start_time', 'end_time'],
            $session->getFillable()
        );
    }

    public function test_session_can_have_null_end_time(): void
    {
        $session = Session::factory()->active()->create();
        
        $this->assertNull($session->end_time);
    }

    public function test_session_can_have_end_time(): void
    {
        $session = Session::factory()->completed()->create();
        
        $this->assertNotNull($session->end_time);
        $this->assertGreaterThan($session->start_time, $session->end_time);
    }
} 