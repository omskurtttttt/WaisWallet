<?php

namespace App\Models;

use App\Models\Concerns\HasUuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Transaction extends Model
{
    use HasFactory, HasUuid;

    /**
     * The "booted" method of the model.
     */
    protected static function booted(): void
    {
        static::creating(function ($transaction) {
            if (empty($transaction->recipient_name)) {
                $transaction->recipient_name = $transaction->title ?? 'N/A';
            }
        });
    }

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'title',
        'type',
        'amount',
        'category_id',
        'card_id',
        'recipient_name',
        'status',
        'transaction_date',
        'notes',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'amount' => 'integer',
            'transaction_date' => 'datetime',
        ];
    }

    /**
     * Get the user that owns the transaction.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the category for the transaction.
     */
    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    /**
     * Get the card associated with the transaction.
     */
    public function card(): BelongsTo
    {
        return $this->belongsTo(Card::class);
    }
}
