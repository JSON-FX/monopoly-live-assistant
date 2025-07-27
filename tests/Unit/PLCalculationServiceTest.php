<?php

namespace Tests\Unit;

use App\Models\Session;
use App\Models\Spin;
use App\Models\User;
use App\Services\PLCalculationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;
use Tests\TestCase;

class PLCalculationServiceTest extends TestCase
{
    use RefreshDatabase;

    private PLCalculationService $service;
    private User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new PLCalculationService();
        $this->user = User::factory()->create();
    }

    /** @test */
    public function it_calculates_total_pl_for_empty_session(): void
    {
        $session = Session::factory()->create(['user_id' => $this->user->id]);

        $totalPL = $this->service->calculateTotalPL($session);

        $this->assertEquals(0, $totalPL);
    }

    /** @test */
    public function it_calculates_total_pl_for_session_with_spins(): void
    {
        $session = Session::factory()->create(['user_id' => $this->user->id]);
        
        Spin::factory()->create(['session_id' => $session->id, 'pl' => 10.50]);
        Spin::factory()->create(['session_id' => $session->id, 'pl' => -5.25]);
        Spin::factory()->create(['session_id' => $session->id, 'pl' => 3.75]);

        $totalPL = $this->service->calculateTotalPL($session);

        $this->assertEquals(9.00, $totalPL);
    }

    /** @test */
    public function it_calculates_running_pl_totals_for_empty_session(): void
    {
        $session = Session::factory()->create(['user_id' => $this->user->id]);

        $runningTotals = $this->service->calculateRunningPLTotals($session);

        $this->assertEmpty($runningTotals);
    }

    /** @test */
    public function it_calculates_running_pl_totals_correctly(): void
    {
        $session = Session::factory()->create(['user_id' => $this->user->id]);
        
        $spin1 = Spin::factory()->create([
            'session_id' => $session->id,
            'pl' => 5.00,
            'created_at' => now()->subMinutes(3)
        ]);
        $spin2 = Spin::factory()->create([
            'session_id' => $session->id,
            'pl' => -2.50,
            'created_at' => now()->subMinutes(2)
        ]);
        $spin3 = Spin::factory()->create([
            'session_id' => $session->id,
            'pl' => 1.25,
            'created_at' => now()->subMinutes(1)
        ]);

        $runningTotals = $this->service->calculateRunningPLTotals($session);

        $expected = [
            [
                'spin_id' => $spin1->id,
                'pl' => 5.00,
                'running_total' => 5.00,
            ],
            [
                'spin_id' => $spin2->id,
                'pl' => -2.50,
                'running_total' => 2.50,
            ],
            [
                'spin_id' => $spin3->id,
                'pl' => 1.25,
                'running_total' => 3.75,
            ],
        ];

        $this->assertEquals($expected, $runningTotals);
    }

    /** @test */
    public function it_generates_empty_session_statistics(): void
    {
        $session = Session::factory()->create(['user_id' => $this->user->id]);

        $stats = $this->service->generateSessionStatistics($session);

        $expected = [
            'total_spins' => 0,
            'winning_spins' => 0,
            'losing_spins' => 0,
            'break_even_spins' => 0,
            'win_rate_percentage' => 0,
            'total_pl' => 0,
            'total_bet_amount' => 0,
            'largest_win' => 0,
            'largest_loss' => 0,
            'average_bet' => 0,
            'average_pl_per_spin' => 0,
            'is_profitable' => false,
        ];

        $this->assertEquals($expected, $stats);
    }

    /** @test */
    public function it_generates_comprehensive_session_statistics(): void
    {
        $session = Session::factory()->create(['user_id' => $this->user->id]);
        
        // Create various spins
        Spin::factory()->create(['session_id' => $session->id, 'pl' => 15.50, 'bet_amount' => 5.00]); // Win
        Spin::factory()->create(['session_id' => $session->id, 'pl' => -5.00, 'bet_amount' => 5.00]); // Loss
        Spin::factory()->create(['session_id' => $session->id, 'pl' => 0.00, 'bet_amount' => 3.00]);  // Break even
        Spin::factory()->create(['session_id' => $session->id, 'pl' => -10.00, 'bet_amount' => 10.00]); // Large loss
        Spin::factory()->create(['session_id' => $session->id, 'pl' => 8.25, 'bet_amount' => 2.75]);   // Win

        $stats = $this->service->generateSessionStatistics($session);

        $this->assertEquals(5, $stats['total_spins']);
        $this->assertEquals(2, $stats['winning_spins']);
        $this->assertEquals(2, $stats['losing_spins']);
        $this->assertEquals(1, $stats['break_even_spins']);
        $this->assertEquals(40.00, $stats['win_rate_percentage']); // 2/5 * 100
        $this->assertEquals(8.75, $stats['total_pl']); // 15.50 - 5 + 0 - 10 + 8.25
        $this->assertEquals(25.75, $stats['total_bet_amount']); // 5 + 5 + 3 + 10 + 2.75
        $this->assertEquals(15.50, $stats['largest_win']);
        $this->assertEquals(-10.00, $stats['largest_loss']);
        $this->assertEquals(5.15, $stats['average_bet']); // 25.75 / 5
        $this->assertEquals(1.75, $stats['average_pl_per_spin']); // 8.75 / 5
        $this->assertTrue($stats['is_profitable']);
    }

    /** @test */
    public function it_handles_unprofitable_sessions(): void
    {
        $session = Session::factory()->create(['user_id' => $this->user->id]);
        
        Spin::factory()->create(['session_id' => $session->id, 'pl' => -5.00, 'bet_amount' => 5.00]);
        Spin::factory()->create(['session_id' => $session->id, 'pl' => -3.00, 'bet_amount' => 3.00]);

        $stats = $this->service->generateSessionStatistics($session);

        $this->assertEquals(-8.00, $stats['total_pl']);
        $this->assertFalse($stats['is_profitable']);
    }

    /** @test */
    public function it_validates_session_data_successfully(): void
    {
        $session = Session::factory()->create(['user_id' => $this->user->id]);
        Spin::factory()->create(['session_id' => $session->id, 'pl' => 5.00, 'bet_amount' => 2.50]);

        // Should not throw exception
        $this->service->validateSessionData($session);
        $this->assertTrue(true); // If we reach here, validation passed
    }

    /** @test */
    public function it_throws_exception_for_non_persisted_session(): void
    {
        $session = new Session();
        $session->user_id = $this->user->id;

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Session must be persisted to database');

        $this->service->validateSessionData($session);
    }

    /** @test */
    public function it_throws_exception_for_session_without_user_id(): void
    {
        $session = Session::factory()->make(['user_id' => null]);
        $session->exists = true; // Mark as persisted but with invalid user_id
        $session->id = 1;

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Session must have a valid user_id');

        $this->service->validateSessionData($session);
    }

    /** @test */
    public function it_throws_exception_for_invalid_spin_data(): void
    {
        $session = Session::factory()->create(['user_id' => $this->user->id]);
        
        // Valid spin
        Spin::factory()->create(['session_id' => $session->id, 'pl' => 5.00, 'bet_amount' => 2.50]);
        
        // Create invalid spin with negative bet_amount (which violates our validation rules)
        Spin::factory()->create(['session_id' => $session->id, 'pl' => -1.00, 'bet_amount' => -1.00]);

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Session contains 1 spins with invalid data');

        $this->service->validateSessionData($session);
    }

    /** @test */
    public function it_throws_exception_for_negative_bet_amounts(): void
    {
        $session = Session::factory()->create(['user_id' => $this->user->id]);
        
        Spin::factory()->create(['session_id' => $session->id, 'pl' => 5.00, 'bet_amount' => -1.00]);

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Session contains 1 spins with invalid data');

        $this->service->validateSessionData($session);
    }

    /** @test */
    public function it_handles_single_spin_session(): void
    {
        $session = Session::factory()->create(['user_id' => $this->user->id]);
        Spin::factory()->create(['session_id' => $session->id, 'pl' => 7.50, 'bet_amount' => 5.00]);

        $totalPL = $this->service->calculateTotalPL($session);
        $runningTotals = $this->service->calculateRunningPLTotals($session);
        $stats = $this->service->generateSessionStatistics($session);

        $this->assertEquals(7.50, $totalPL);
        $this->assertCount(1, $runningTotals);
        $this->assertEquals(7.50, $runningTotals[0]['running_total']);
        $this->assertEquals(1, $stats['total_spins']);
        $this->assertEquals(100.00, $stats['win_rate_percentage']);
    }

    /** @test */
    public function it_handles_decimal_precision_correctly(): void
    {
        $session = Session::factory()->create(['user_id' => $this->user->id]);
        
        Spin::factory()->create(['session_id' => $session->id, 'pl' => 1.234, 'bet_amount' => 2.567]);
        Spin::factory()->create(['session_id' => $session->id, 'pl' => -0.789, 'bet_amount' => 1.111]);

        $stats = $this->service->generateSessionStatistics($session);

        // Verify rounding is applied correctly
        $this->assertEquals(1.84, $stats['average_bet']); // (2.567 + 1.111) / 2 = 1.839 rounded to 1.84
        $this->assertEquals(0.22, $stats['average_pl_per_spin']); // (1.234 - 0.789) / 2 = 0.2225 rounded to 0.22
    }
} 