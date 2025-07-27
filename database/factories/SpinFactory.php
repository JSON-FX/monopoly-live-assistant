<?php

namespace Database\Factories;

use App\Models\Session;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Spin>
 */
class SpinFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $results = ['1', '2', '5', '10', 'Chance', '2 Rolls', '4 Rolls'];
        $betAmount = $this->faker->randomFloat(2, 1, 100);
        $result = $this->faker->randomElement($results);
        
        // Generate realistic P/L based on result and bet amount
        $pl = $this->calculatePL($result, $betAmount);
        
        return [
            'session_id' => Session::factory(),
            'result' => $result,
            'bet_amount' => $betAmount,
            'pl' => $pl,
        ];
    }

    /**
     * Create a winning spin.
     */
    public function winning(): static
    {
        return $this->state(function (array $attributes) {
            $betAmount = $attributes['bet_amount'] ?? 10.00;
            return [
                'result' => $this->faker->randomElement(['1', '2', '5', '10']),
                'pl' => $this->faker->randomFloat(2, $betAmount * 0.5, $betAmount * 2),
            ];
        });
    }

    /**
     * Create a losing spin.
     */
    public function losing(): static
    {
        return $this->afterMaking(function ($spin) {
            $spin->pl = -$spin->bet_amount;
        });
    }

    /**
     * Calculate realistic P/L based on result and bet amount.
     */
    private function calculatePL(string $result, float $betAmount): float
    {
        // Simplified P/L calculation for test data
        return match($result) {
            '1' => $this->faker->boolean(60) ? $betAmount : -$betAmount,
            '2' => $this->faker->boolean(50) ? $betAmount * 2 : -$betAmount,
            '5' => $this->faker->boolean(40) ? $betAmount * 5 : -$betAmount,
            '10' => $this->faker->boolean(30) ? $betAmount * 10 : -$betAmount,
            'Chance', '2 Rolls', '4 Rolls' => $this->faker->randomFloat(2, -$betAmount, $betAmount * 20),
            default => -$betAmount,
        };
    }
}
