<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Session>
 */
class SessionFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $startTime = $this->faker->dateTimeBetween('-1 month', '-1 day');
        
        return [
            'user_id' => User::factory(),
            'start_time' => $startTime,
            'end_time' => $this->faker->optional(0.7)->dateTimeBetween($startTime, 'now'),
        ];
    }

    /**
     * Create an active session (no end_time).
     */
    public function active(): static
    {
        return $this->state(fn (array $attributes) => [
            'end_time' => null,
        ]);
    }

    /**
     * Create a completed session with end_time.
     */
    public function completed(): static
    {
        return $this->state(function (array $attributes) {
            $startTime = $attributes['start_time'] ?? $this->faker->dateTimeBetween('-1 month', '-1 day');
            return [
                'end_time' => $this->faker->dateTimeBetween($startTime, 'now'),
            ];
        });
    }
}
