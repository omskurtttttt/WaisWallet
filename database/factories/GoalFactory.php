<?php

namespace Database\Factories;

use App\Models\Goal;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Goal>
 */
class GoalFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $target = fake()->numberBetween(50000, 1000000); // $500.00 to $10,000.00

        return [
            'user_id' => User::factory(),
            'name' => fake()->words(3, true),
            'target_amount' => $target,
            'current_amount' => fake()->numberBetween(0, $target),
            'target_date' => fake()->dateTimeBetween('+1 month', '+2 years'),
        ];
    }
}
