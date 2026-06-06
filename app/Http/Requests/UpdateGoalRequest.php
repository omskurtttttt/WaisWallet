<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateGoalRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        if ($this->has('target_amount')) {
            $this->merge([
                'target_amount' => (int) round($this->target_amount * 100),
            ]);
        }
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'target_amount' => ['required', 'integer', 'min:1', 'max:9999999900'],
            'target_date' => ['nullable', 'date'],
        ];
    }
}
