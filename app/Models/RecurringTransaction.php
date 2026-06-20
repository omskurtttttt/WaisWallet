<?php

namespace App\Models;

use App\Models\Concerns\HasUuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class RecurringTransaction extends Model
{
    use HasFactory, HasUuid, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'type',
        'amount',
        'category_id',
        'recipient_name',
        'title',
        'card_id',
        'frequency',
        'next_run_date',
        'last_run_date',
        'is_active',
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
            'next_run_date' => 'date',
            'last_run_date' => 'date',
            'is_active' => 'boolean',
        ];
    }

    /**
     * Get the user that owns the recurring transaction template.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the category for the template.
     */
    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    /**
     * Get the card associated with the template.
     */
    public function card(): BelongsTo
    {
        return $this->belongsTo(Card::class);
    }
}
