<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreGoalRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'target_amount' => ['required', 'numeric', 'min:0.01', 'max:99999999'],
            'current_amount' => ['nullable', 'numeric', 'min:0', 'max:99999999'],
            'target_date' => ['nullable', 'date', 'after_or_equal:today'],
        ];
    }
}
