<?php

namespace Database\Factories;

use App\Models\Category;
use App\Models\User;
use App\Models\Card;
use App\Models\RecurringTransaction;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\RecurringTransaction>
 */
class RecurringTransactionFactory extends Factory
{
    protected $model = RecurringTransaction::class;

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
            'frequency' => fake()->randomElement(['daily', 'weekly', 'bi-weekly', 'monthly', 'yearly']),
            'next_run_date' => fake()->dateTimeBetween('now', '+1 month')->format('Y-m-d'),
            'last_run_date' => null,
            'is_active' => true,
            'notes' => fake()->optional(0.7)->sentence(),
        ];
    }
}
