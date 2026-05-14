<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Card>
 */
class CardFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'last_four' => fake()->numerify('####'),
            'cardholder_name' => fake()->name(),
            'expiry_date' => fake()->numberBetween(1, 12) . '/' . fake()->numberBetween(25, 30),
            'card_type' => fake()->randomElement(['visa', 'mastercard', 'amex', 'other']),
            'nickname' => fake()->optional(0.5)->word(),
        ];
    }
}
