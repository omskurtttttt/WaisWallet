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
        Category::seedDefaultsForUser($user);
        $createdCategories = $user->categories()->get()->keyBy('name');

        // 3. Create Cards
        $card1 = Card::factory()->create([
            'user_id' => $user->id,
            'brand' => 'Visa',
            'last_four' => '4532',
            'name' => 'Main Checking',
            'expiration_month' => 12,
            'expiration_year' => 2028,
            'color' => '#7033ff',
            'is_active' => true,
        ]);
        $card2 = Card::factory()->create([
            'user_id' => $user->id,
            'brand' => 'Mastercard',
            'last_four' => '8891',
            'name' => 'Travel Rewards',
            'expiration_month' => 6,
            'expiration_year' => 2029,
            'color' => '#fd822b',
            'is_active' => true,
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
            '🏠 Bills & Utilities' => 150000,
            '🍔 Food & Dining' => 60000,
            '🚗 Transportation' => 30000,
            '🛍 Shopping' => 40000,
        ];

        foreach ($budgetAllocations as $catName => $amount) {
            if (isset($createdCategories[$catName])) {
                Budget::factory()->create([
                    'user_id' => $user->id,
                    'category_id' => $createdCategories[$catName]->id,
                    'allocated_amount' => $amount,
                    'month' => $currentMonth,
                ]);
            }
        }

        // 6. Create Transactions (mix of income and expenses over last 3 months)
        $expenseCategories = $createdCategories->filter(fn ($c) => $c->type === 'expense')->all();
        $incomeCategories = $createdCategories->filter(fn ($c) => $c->type === 'income')->all();

        // Generate 50 random expenses
        $cardOptions = [$card1->id, $card2->id, null];
        $transactionNames = [
            '🏠 Bills & Utilities' => ['Rent Payment', 'Home Maintenance', 'Internet Bill', 'Electric Bill', 'Water Bill', 'Gas Utility'],
            '🍔 Food & Dining' => ['Supermarket Weekly', 'Corner Bakery', 'Starbucks Coffee', 'Dinner with Friends'],
            '🚗 Transportation' => ['Gas Station Refill', 'Uber Ride', 'Bus Ticket', 'Train Fare'],
            '🛍 Shopping' => ['Target Store', 'Amazon Order', 'H&M Clothing', 'Electronics Upgrade'],
            '🎮 Entertainment' => ['Netflix Subscription', 'Movie Night', 'Concert Ticket', 'Spotify Family'],
        ];

        for ($i = 0; $i < 50; $i++) {
            $cat = $expenseCategories[array_rand($expenseCategories)];
            $nameOptions = $transactionNames[$cat->name] ?? ['Expense Purchase'];
            $title = $nameOptions[array_rand($nameOptions)];

            Transaction::factory()->create([
                'user_id' => $user->id,
                'category_id' => $cat->id,
                'card_id' => $cardOptions[array_rand($cardOptions)],
                'type' => 'expense',
                'title' => $title,
                'amount' => rand(1000, 25000), // $10 to $250
                'transaction_date' => Carbon::now()->subDays(rand(0, 90)),
            ]);
        }

        // Generate 6 income transactions (e.g. bi-weekly salary)
        for ($i = 0; $i < 6; $i++) {
            Transaction::factory()->create([
                'user_id' => $user->id,
                'category_id' => $createdCategories['💼 Salary']->id,
                'card_id' => null,
                'type' => 'income',
                'title' => 'Bi-weekly Salary',
                'amount' => 350000, // $3,500.00
                'recipient_name' => 'Acme Corp Inc.',
                'transaction_date' => Carbon::now()->subDays($i * 14),
            ]);
        }
    }
}
