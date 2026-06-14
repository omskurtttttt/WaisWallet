<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreBudgetRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'month' => ['required', 'date_format:Y-m'],
            'budgets' => ['required', 'array'],
            'budgets.*.category_id' => [
                'required',
                'uuid',
                Rule::exists('categories', 'id')->where(function ($query) {
                    $query->where('user_id', $this->user()->id);
                }),
            ],
            'budgets.*.amount' => ['nullable', 'numeric', 'min:0'],
        ];
    }
}
