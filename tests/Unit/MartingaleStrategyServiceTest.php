<?php

namespace Tests\Unit;

use App\Models\Session;
use App\Models\Spin;
use App\Models\User;
use App\Services\MartingaleStrategyService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use InvalidArgumentException;
use Tests\TestCase;

class MartingaleStrategyServiceTest extends TestCase
{
    use RefreshDatabase;

    private MartingaleStrategyService $service;
    private User $user;
    private Session $session;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new MartingaleStrategyService();
        $this->user = User::factory()->create();
        $this->session = Session::factory()->create(['user_id' => $this->user->id]);
    }

    /** @test */
    public function it_determines_bet_action_for_empty_session(): void
    {
        $action = $this->service->determineNextAction($this->session);

        $this->assertEquals('Bet', $action['action']);
        $this->assertEquals(1.00, $action['bet_amount']);
        $this->assertEquals('First spin - start with base bet', $action['reason']);
        $this->assertEquals(0, $action['consecutive_losses']);
    }

    /** @test */
    public function it_determines_bet_action_after_winning_spin(): void
    {
        Spin::factory()->create([
            'session_id' => $this->session->id,
            'result' => '1',
            'bet_amount' => 5.00,
            'pl' => 10.00,
        ]);

        $action = $this->service->determineNextAction($this->session, 2.00);

        $this->assertEquals('Bet', $action['action']);
        $this->assertEquals(2.00, $action['bet_amount']); // Reset to base bet after win
        $this->assertEquals('Previous spin won - reset to base bet', $action['reason']);
        $this->assertEquals(0, $action['consecutive_losses']);
    }

    /** @test */
    public function it_determines_bet_action_after_losing_spin(): void
    {
        Spin::factory()->create([
            'session_id' => $this->session->id,
            'result' => '2',
            'bet_amount' => 1.00,
            'pl' => -1.00,
        ]);

        $action = $this->service->determineNextAction($this->session);

        $this->assertEquals('Bet', $action['action']);
        $this->assertEquals(2.00, $action['bet_amount']); // Double after loss
        $this->assertEquals('Martingale progression after 1 consecutive losses', $action['reason']);
        $this->assertEquals(1, $action['consecutive_losses']);
    }

    /** @test */
    public function it_determines_skip_action_when_bet_exceeds_maximum(): void
    {
        // Create 4 consecutive losses: 1, 2, 4, 8, next would be 16 > 10 max
        Spin::factory()->create(['session_id' => $this->session->id, 'result' => '2', 'bet_amount' => 1.00, 'pl' => -1.00]);
        Spin::factory()->create(['session_id' => $this->session->id, 'result' => '3', 'bet_amount' => 2.00, 'pl' => -2.00]);
        Spin::factory()->create(['session_id' => $this->session->id, 'result' => '4', 'bet_amount' => 4.00, 'pl' => -4.00]);
        Spin::factory()->create(['session_id' => $this->session->id, 'result' => '5', 'bet_amount' => 8.00, 'pl' => -8.00]);

        $action = $this->service->determineNextAction($this->session, 1.00, 10.00);

        $this->assertEquals('Skip', $action['action']);
        $this->assertEquals(0, $action['bet_amount']);
        $this->assertEquals('Next bet amount (16) exceeds maximum bet limit (10)', $action['reason']);
        $this->assertEquals(4, $action['consecutive_losses']);
    }

    /** @test */
    public function it_calculates_next_bet_amount_correctly(): void
    {
        $this->assertEquals(1.00, $this->service->calculateNextBetAmount(1.00, 0));
        $this->assertEquals(2.00, $this->service->calculateNextBetAmount(1.00, 1));
        $this->assertEquals(4.00, $this->service->calculateNextBetAmount(1.00, 2));
        $this->assertEquals(8.00, $this->service->calculateNextBetAmount(1.00, 3));
        $this->assertEquals(16.00, $this->service->calculateNextBetAmount(1.00, 4));
        
        // Test with different base bet
        $this->assertEquals(5.00, $this->service->calculateNextBetAmount(5.00, 0));
        $this->assertEquals(10.00, $this->service->calculateNextBetAmount(5.00, 1));
        $this->assertEquals(20.00, $this->service->calculateNextBetAmount(5.00, 2));
    }

    /** @test */
    public function it_throws_exception_for_negative_consecutive_losses(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Consecutive losses cannot be negative');

        $this->service->calculateNextBetAmount(1.00, -1);
    }

    /** @test */
    public function it_throws_exception_for_non_positive_base_bet(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Base bet must be positive');

        $this->service->calculateNextBetAmount(0, 1);
    }

    /** @test */
    public function it_gets_consecutive_losses_from_empty_session(): void
    {
        $spins = collect();

        $consecutiveLosses = $this->service->getConsecutiveLossesFromEnd($spins);

        $this->assertEquals(0, $consecutiveLosses);
    }

    /** @test */
    public function it_gets_consecutive_losses_correctly(): void
    {
        $spins = collect([
            Spin::factory()->make(['result' => '1', 'bet_amount' => 1.00, 'pl' => 2.00]), // Win
            Spin::factory()->make(['result' => '2', 'bet_amount' => 1.00, 'pl' => -1.00]), // Loss
            Spin::factory()->make(['result' => '3', 'bet_amount' => 2.00, 'pl' => -2.00]), // Loss
            Spin::factory()->make(['result' => '4', 'bet_amount' => 4.00, 'pl' => -4.00]), // Loss
        ]);

        $consecutiveLosses = $this->service->getConsecutiveLossesFromEnd($spins);

        $this->assertEquals(3, $consecutiveLosses); // Last 3 spins were losses
    }

    /** @test */
    public function it_gets_zero_consecutive_losses_when_last_spin_won(): void
    {
        $spins = collect([
            Spin::factory()->make(['result' => '2', 'bet_amount' => 1.00, 'pl' => -1.00]), // Loss
            Spin::factory()->make(['result' => '3', 'bet_amount' => 2.00, 'pl' => -2.00]), // Loss
            Spin::factory()->make(['result' => '1', 'bet_amount' => 4.00, 'pl' => 8.00]), // Win
        ]);

        $consecutiveLosses = $this->service->getConsecutiveLossesFromEnd($spins);

        $this->assertEquals(0, $consecutiveLosses);
    }

    /** @test */
    public function it_identifies_winning_spin_correctly(): void
    {
        $winningSpin = Spin::factory()->make(['result' => '1', 'pl' => 5.00]);
        $losingSpinWrongResult = Spin::factory()->make(['result' => '2', 'pl' => 5.00]);
        $losingSpinNegativePL = Spin::factory()->make(['result' => '1', 'pl' => -5.00]);

        $this->assertTrue($this->service->isWinningSpin($winningSpin));
        $this->assertFalse($this->service->isWinningSpin($losingSpinWrongResult));
        $this->assertFalse($this->service->isWinningSpin($losingSpinNegativePL));
    }

    /** @test */
    public function it_identifies_losing_bet_correctly(): void
    {
        $losingBet = Spin::factory()->make(['bet_amount' => 5.00, 'pl' => -5.00]);
        $winningBet = Spin::factory()->make(['bet_amount' => 5.00, 'pl' => 10.00]);
        $noBet = Spin::factory()->make(['bet_amount' => 0, 'pl' => -1.00]);

        $this->assertTrue($this->service->isLosingBet($losingBet));
        $this->assertFalse($this->service->isLosingBet($winningBet));
        $this->assertFalse($this->service->isLosingBet($noBet));
    }

    /** @test */
    public function it_validates_strategy_parameters_successfully(): void
    {
        // Should not throw exception
        $this->service->validateStrategyParameters(1.00, 100.00);
        $this->assertTrue(true);
    }

    /** @test */
    public function it_throws_exception_for_non_positive_base_bet_in_validation(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Base bet must be positive');

        $this->service->validateStrategyParameters(0, 100.00);
    }

    /** @test */
    public function it_throws_exception_for_non_positive_max_bet(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Maximum bet must be positive');

        $this->service->validateStrategyParameters(1.00, 0);
    }

    /** @test */
    public function it_throws_exception_when_base_bet_exceeds_max_bet(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Base bet cannot exceed maximum bet');

        $this->service->validateStrategyParameters(10.00, 5.00);
    }

    /** @test */
    public function it_calculates_max_consecutive_losses_correctly(): void
    {
        // Base bet 1, max bet 8: 1, 2, 4, 8 -> 3 losses max
        $maxLosses = $this->service->calculateMaxConsecutiveLosses(1.00, 8.00);
        $this->assertEquals(3, $maxLosses);

        // Base bet 2, max bet 16: 2, 4, 8, 16 -> 3 losses max
        $maxLosses = $this->service->calculateMaxConsecutiveLosses(2.00, 16.00);
        $this->assertEquals(3, $maxLosses);

        // Base bet 1, max bet 15: 1, 2, 4, 8, (16 > 15) -> 3 losses max
        $maxLosses = $this->service->calculateMaxConsecutiveLosses(1.00, 15.00);
        $this->assertEquals(3, $maxLosses);
    }

    /** @test */
    public function it_gets_strategy_configuration(): void
    {
        $config = $this->service->getStrategyConfiguration(2.00, 50.00);

        $expected = [
            'strategy_name' => 'Martingale "Bet on 1"',
            'base_bet' => 2.00,
            'max_bet' => 50.00,
            'winning_result' => '1',
            'max_consecutive_losses' => 4, // 2, 4, 8, 16, 32, (64 > 50) -> 4 losses max
            'progression_type' => 'Double on loss, reset on win',
        ];

        $this->assertEquals($expected, $config);
    }

    /** @test */
    public function it_handles_mixed_spin_sequence(): void
    {
        // Create a mixed sequence: Loss, Win, Loss, Loss
        Spin::factory()->create(['session_id' => $this->session->id, 'result' => '2', 'bet_amount' => 1.00, 'pl' => -1.00]);
        Spin::factory()->create(['session_id' => $this->session->id, 'result' => '1', 'bet_amount' => 2.00, 'pl' => 4.00]);
        Spin::factory()->create(['session_id' => $this->session->id, 'result' => '3', 'bet_amount' => 1.00, 'pl' => -1.00]);
        Spin::factory()->create(['session_id' => $this->session->id, 'result' => '4', 'bet_amount' => 2.00, 'pl' => -2.00]);

        $action = $this->service->determineNextAction($this->session);

        $this->assertEquals('Bet', $action['action']);
        $this->assertEquals(4.00, $action['bet_amount']); // Double after 2 consecutive losses
        $this->assertEquals(2, $action['consecutive_losses']); // Only count from last win
    }

    /** @test */
    public function it_handles_break_even_spins(): void
    {
        // Break-even spin (not a loss, not a win)
        Spin::factory()->create(['session_id' => $this->session->id, 'result' => '5', 'bet_amount' => 1.00, 'pl' => 0.00]);
        
        $action = $this->service->determineNextAction($this->session);

        $this->assertEquals('Bet', $action['action']);
        $this->assertEquals(1.00, $action['bet_amount']); // Base bet since no consecutive losses
        $this->assertEquals(0, $action['consecutive_losses']);
    }

    /** @test */
    public function it_handles_edge_case_with_custom_parameters(): void
    {
        $action = $this->service->determineNextAction($this->session, 5.00, 20.00);

        $this->assertEquals('Bet', $action['action']);
        $this->assertEquals(5.00, $action['bet_amount']);
        $this->assertEquals('First spin - start with base bet', $action['reason']);
    }
} 