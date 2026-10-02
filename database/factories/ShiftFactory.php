<?php

namespace Database\Factories;

use App\Models\Shift;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Shift>
 */
class ShiftFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory()->kasir(),
            'opening_cash' => fake()->numberBetween(1, 10) * 100000,
            'closing_cash' => null,
            'expected_cash' => null,
            'status' => Shift::STATUS_OPEN,
            'opened_at' => now(),
            'closed_at' => null,
        ];
    }

    /**
     * Shift yang sudah ditutup (closing/expected terisi, closed_at ada).
     */
    public function closed(): static
    {
        return $this->state(fn (array $attributes) => [
            'closing_cash' => $attributes['opening_cash'],
            'expected_cash' => $attributes['opening_cash'],
            'status' => Shift::STATUS_CLOSED,
            'closed_at' => now(),
        ]);
    }
}
