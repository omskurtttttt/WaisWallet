<?php

namespace App\Models;

use App\Models\Concerns\HasUuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Card extends Model
{
    use HasFactory, HasUuid, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'brand',
        'last_four',
        'expiration_month',
        'expiration_year',
        'color',
        'is_active',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'expiration_month' => 'integer',
            'expiration_year' => 'integer',
        ];
    }

    /**
     * Scope a query to include the calculated balance for each card.
     */
    public function scopeWithBalance($query)
    {
        return $query
            ->withSum(['transactions as income_sum' => fn ($q) => $q->where('type', 'income')], 'amount')
            ->withSum(['transactions as expense_sum' => fn ($q) => $q->where('type', 'expense')], 'amount');
    }

    /**
     * Get the user that owns the card.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the transactions associated with the card.
     */
    public function transactions(): HasMany
    {
        return $this->hasMany(Transaction::class);
    }
}
