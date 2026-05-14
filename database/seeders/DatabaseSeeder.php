<?php

namespace Database\Seeders;

use App\Models\Budget;
use App\Models\Card;
use App\Models\Category;
use App\Models\Goal;
use App\Models\Transaction;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // 1. Create default user
        $user = User::factory()->create([
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'password' => Hash::make('password'),
            'monthly_spending_limit' => 500000, // $5,000.00
        ]);

        // 2. Create Categories
        $categories = [
            // Income
            ['name' => 'Salary', 'type' => 'income', 'icon' => 'briefcase'],
            ['name' => 'Freelance', 'type' => 'income', 'icon' => 'monitor'],
            ['name' => 'Investments', 'type' => 'income', 'icon' => 'trending-up'],
            // Expense / Budget
            ['name' => 'Housing', 'type' => 'expense', 'icon' => 'home'],
            ['name' => 'Food & Dining', 'type' => 'expense', 'icon' => 'coffee'],
            ['name' => 'Transportation', 'type' => 'expense', 'icon' => 'car'],
            ['name' => 'Shopping', 'type' => 'expense', 'icon' => 'shopping-cart'],
            ['name' => 'Entertainment', 'type' => 'expense', 'icon' => 'film'],
            ['name' => 'Utilities', 'type' => 'expense', 'icon' => 'zap'],
        ];

        $createdCategories = [];
        foreach ($categories as $catData) {
            $createdCategories[$catData['name']] = Category::factory()->create([
                'user_id' => $user->id,
                'name' => $catData['name'],
                'type' => $catData['type'],
                'icon' => $catData['icon'],
            ]);
        }

        // 3. Create Cards
        Card::factory()->create([
            'user_id' => $user->id,
            'card_type' => 'visa',
            'last_four' => '4532',
            'cardholder_name' => 'John Doe',
            'nickname' => 'Main Checking',
        ]);
        Card::factory()->create([
            'user_id' => $user->id,
            'card_type' => 'mastercard',
            'last_four' => '8891',
            'cardholder_name' => 'John Doe',
            'nickname' => 'Travel Rewards',
        ]);

        // 4. Create Goals
        Goal::factory()->create([
            'user_id' => $user->id,
            'name' => 'Emergency Fund',
            'target_amount' => 1000000, // $10,000.00
            'current_amount' => 650000, // $6,500.00
            'target_date' => Carbon::now()->addMonths(6),
        ]);
        Goal::factory()->create([
            'user_id' => $user->id,
            'name' => 'New Car Downpayment',
            'target_amount' => 500000, // $5,000.00
            'current_amount' => 125000, // $1,250.00
            'target_date' => Carbon::now()->addYear(),
        ]);

        // 5. Create Budgets for current month
        $currentMonth = Carbon::now()->startOfMonth()->format('Y-m-d');
        $budgetAllocations = [
            'Housing' => 150000, // $1,500.00
            'Food & Dining' => 60000, // $600.00
            'Transportation' => 30000, // $300.00
            'Shopping' => 40000, // $400.00
        ];

        foreach ($budgetAllocations as $catName => $amount) {
            Budget::factory()->create([
                'user_id' => $user->id,
                'category_id' => $createdCategories[$catName]->id,
                'allocated_amount' => $amount,
                'month' => $currentMonth,
            ]);
        }

        // 6. Create Transactions (mix of income and expenses over last 3 months)
        $expenseCategories = array_filter($createdCategories, fn($c) => $c->type === 'expense');
        $incomeCategories = array_filter($createdCategories, fn($c) => $c->type === 'income');

        // Generate 50 random expenses
        for ($i = 0; $i < 50; $i++) {
            $cat = $expenseCategories[array_rand($expenseCategories)];
            Transaction::factory()->create([
                'user_id' => $user->id,
                'category_id' => $cat->id,
                'type' => 'expense',
                'amount' => rand(1000, 25000), // $10 to $250
                'transaction_date' => Carbon::now()->subDays(rand(0, 90)),
            ]);
        }

        // Generate 6 income transactions (e.g. bi-weekly salary)
        for ($i = 0; $i < 6; $i++) {
            Transaction::factory()->create([
                'user_id' => $user->id,
                'category_id' => $createdCategories['Salary']->id,
                'type' => 'income',
                'amount' => 350000, // $3,500.00
                'recipient_name' => 'Acme Corp Inc.',
                'transaction_date' => Carbon::now()->subDays($i * 14),
            ]);
        }
    }
}
