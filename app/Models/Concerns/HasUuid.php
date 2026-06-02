<?php

namespace App\Models\Concerns;

use Illuminate\Support\Str;

trait HasUuid
{
    /**
     * Boot the HasUuid trait.
     */
    protected static function bootHasUuid(): void
    {
        static::creating(function ($model) {
            if (empty($model->{$model->getKeyName()})) {
                $model->{$model->getKeyName()} = (string) Str::uuid();
            }
        });
    }

    /**
     * Initialize the HasUuid trait.
     */
    public function initializeHasUuid(): void
    {
        $this->keyType = 'string';
        $this->incrementing = false;
    }

    /**
     * Get the auto-incrementing key type.
     */
    public function getKeyType(): string
    {
        return 'string';
    }

    /**
     * Indicates if the IDs are auto-incrementing.
     */
    public function getIncrementing(): bool
    {
        return false;
    }
}
