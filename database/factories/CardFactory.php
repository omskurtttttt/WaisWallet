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
            'name' => fake()->words(2, true) . ' Card',
            'brand' => fake()->randomElement(['Visa', 'Mastercard', 'Amex', 'Discover', 'Other']),
            'last_four' => fake()->numerify('####'),
            'expiration_month' => fake()->numberBetween(1, 12),
            'expiration_year' => (int) date('Y') + fake()->numberBetween(1, 5),
            'color' => fake()->hexColor(),
            'is_active' => true,
        ];
    }
}
