<?php

namespace Tests\Unit;

use App\Models\Session;
use App\Models\Spin;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SpinTest extends TestCase
{
    use RefreshDatabase;

    public function test_spin_can_be_created_with_valid_attributes(): void
    {
        $session = Session::factory()->create();
        
        $spin = Spin::create([
            'session_id' => $session->id,
            'result' => '1',
            'bet_amount' => 10.50,
            'pl' => -10.50,
        ]);

        $this->assertInstanceOf(Spin::class, $spin);
        $this->assertEquals($session->id, $spin->session_id);
        $this->assertEquals('1', $spin->result);
        $this->assertEquals(10.50, $spin->bet_amount);
        $this->assertEquals(-10.50, $spin->pl);
    }

    public function test_spin_belongs_to_session(): void
    {
        $session = Session::factory()->create();
        $spin = Spin::factory()->create(['session_id' => $session->id]);

        $this->assertInstanceOf(Session::class, $spin->session);
        $this->assertEquals($session->id, $spin->session->id);
    }

    public function test_spin_casts_decimals_correctly(): void
    {
        $spin = Spin::factory()->create([
            'bet_amount' => '15.75',
            'pl' => '-15.75',
        ]);

        // Laravel decimal casting returns strings, not floats
        $this->assertIsString($spin->bet_amount);
        $this->assertIsString($spin->pl);
        $this->assertEquals('15.75', $spin->bet_amount);
        $this->assertEquals('-15.75', $spin->pl);
    }

    public function test_spin_validation_rules_are_correct(): void
    {
        $rules = Spin::validationRules();

        $this->assertArrayHasKey('session_id', $rules);
        $this->assertArrayHasKey('result', $rules);
        $this->assertArrayHasKey('bet_amount', $rules);
        $this->assertArrayHasKey('pl', $rules);
        
        $this->assertStringContainsString('required', $rules['session_id']);
        $this->assertStringContainsString('exists:game_sessions,id', $rules['session_id']);
        $this->assertStringContainsString('required', $rules['result']);
        $this->assertStringContainsString('string', $rules['result']);
        $this->assertStringContainsString('required', $rules['bet_amount']);
        $this->assertStringContainsString('numeric', $rules['bet_amount']);
        $this->assertStringContainsString('min:0', $rules['bet_amount']);
        $this->assertStringContainsString('required', $rules['pl']);
        $this->assertStringContainsString('numeric', $rules['pl']);
    }

    public function test_spin_fillable_attributes(): void
    {
        $spin = new Spin();
        
        $this->assertEquals(
            ['session_id', 'result', 'bet_amount', 'pl'],
            $spin->getFillable()
        );
    }

    public function test_spin_factory_creates_realistic_monopoly_results(): void
    {
        $spin = Spin::factory()->create();
        
        $validResults = ['1', '2', '5', '10', 'Chance', '2 Rolls', '4 Rolls'];
        $this->assertContains($spin->result, $validResults);
        // Decimal cast returns strings
        $this->assertIsString($spin->bet_amount);
        $this->assertIsString($spin->pl);
    }

    public function test_spin_factory_winning_state(): void
    {
        $spin = Spin::factory()->winning()->create(['bet_amount' => 10.00]);
        
        $validWinningResults = ['1', '2', '5', '10'];
        $this->assertContains($spin->result, $validWinningResults);
        $this->assertGreaterThan(0, (float)$spin->pl);
    }

    public function test_spin_factory_losing_state(): void
    {
        $spin = Spin::factory()->losing()->create(['bet_amount' => 10.00]);
        
        $this->assertEquals('-10.00', $spin->pl);
    }

    public function test_spin_can_have_positive_and_negative_pl(): void
    {
        $winSpin = Spin::factory()->create(['pl' => 25.00]);
        $lossSpin = Spin::factory()->create(['pl' => -15.00]);
        
        $this->assertGreaterThan(0, (float)$winSpin->pl);
        $this->assertLessThan(0, (float)$lossSpin->pl);
    }

    public function test_spin_bet_amount_precision(): void
    {
        $spin = Spin::factory()->create(['bet_amount' => 12.345]);
        
        // Should be rounded to 2 decimal places due to decimal(10,2) casting
        $this->assertEquals('12.35', $spin->bet_amount);
    }
} 