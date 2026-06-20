<?php

namespace App\Models;

use App\Models\Concerns\HasUuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Category extends Model
{
    use HasFactory, HasUuid;

    /**
     * Seed default categories for a user.
     */
    public static function seedDefaultsForUser(User $user): void
    {
        $defaultCategories = [
            // Expense Categories
            ['name' => '🍔 Food & Dining', 'type' => 'expense', 'icon' => 'coffee', 'color' => '#fd822b'],
            ['name' => '🛒 Groceries', 'type' => 'expense', 'icon' => 'shopping-cart', 'color' => '#4ac885'],
            ['name' => '🚗 Transportation', 'type' => 'expense', 'icon' => 'car', 'color' => '#3276e4'],
            ['name' => '🏠 Bills & Utilities', 'type' => 'expense', 'icon' => 'zap', 'color' => '#7033ff'],
            ['name' => '🎮 Entertainment', 'type' => 'expense', 'icon' => 'film', 'color' => '#e54b4f'],
            ['name' => '🏥 Health', 'type' => 'expense', 'icon' => 'heart-pulse', 'color' => '#ff477e'],
            ['name' => '📚 Education', 'type' => 'expense', 'icon' => 'graduation-cap', 'color' => '#fdbe2a'],
            ['name' => '🛍 Shopping', 'type' => 'expense', 'icon' => 'shopping-bag', 'color' => '#747474'],
            ['name' => '✈ Travel', 'type' => 'expense', 'icon' => 'plane', 'color' => '#38bdf8'],
            ['name' => '💸 Others', 'type' => 'expense', 'icon' => 'tag', 'color' => '#a0a0a0'],

            // Income Categories
            ['name' => '💼 Salary', 'type' => 'income', 'icon' => 'briefcase', 'color' => '#4ac885'],
            ['name' => '🧑‍💻 Freelance', 'type' => 'income', 'icon' => 'monitor', 'color' => '#3276e4'],
            ['name' => '📈 Investments', 'type' => 'income', 'icon' => 'trending-up', 'color' => '#fd822b'],
            ['name' => '🎁 Gifts', 'type' => 'income', 'icon' => 'gift', 'color' => '#ff477e'],
            ['name' => '💰 Allowance', 'type' => 'income', 'icon' => 'briefcase', 'color' => '#fdbe2a'],
            ['name' => '🪙 Side Hustle', 'type' => 'income', 'icon' => 'trending-up', 'color' => '#8c5cff'],
        ];

        foreach ($defaultCategories as $category) {
            $user->categories()->create($category);
        }
    }

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'type',
        'icon',
        'color',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [];
    }

    /**
     * Get the user that owns the category.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the transactions for the category.
     */
    public function transactions(): HasMany
    {
        return $this->hasMany(Transaction::class);
    }

    /**
     * Get the budgets for the category.
     */
    public function budgets(): HasMany
    {
        return $this->hasMany(Budget::class);
    }
}
