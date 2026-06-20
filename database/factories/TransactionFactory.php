<?php

namespace Database\Factories;

use App\Models\Category;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Transaction>
 */
class TransactionFactory extends Factory
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
            'title' => fake()->sentence(3),
            'type' => fake()->randomElement(['income', 'expense']),
            'amount' => fake()->numberBetween(1000, 150000), // $10.00 to $1,500.00
            'category_id' => Category::factory(),
            'card_id' => null,
            'recipient_name' => fake()->company(),
            'status' => fake()->randomElement(['completed', 'completed', 'completed', 'pending', 'failed']),
            'transaction_date' => fake()->dateTimeBetween('-3 months', 'now'),
            'notes' => fake()->optional(0.7)->sentence(),
        ];
    }
}
