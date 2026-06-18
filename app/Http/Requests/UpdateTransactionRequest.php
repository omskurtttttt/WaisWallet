<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateTransactionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        if ($this->has('amount')) {
            $this->merge([
                'amount' => (int) round($this->amount * 100),
            ]);
        }
    }

    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:255'],
            'amount' => ['required', 'integer', 'min:1', 'max:9999999900'],
            'type' => ['required', 'string', Rule::in(['income', 'expense'])],
            'status' => ['required', 'string', Rule::in(['completed', 'pending', 'failed'])],
            'transaction_date' => ['required', 'date'],
            'category_id' => [
                'required',
                'string',
                Rule::exists('categories', 'id')->where(function ($query) {
                    $query->where('user_id', $this->user()->id);
                }),
            ],
            'card_id' => [
                'nullable',
                'string',
                Rule::exists('cards', 'id')->where(function ($query) {
                    $query->where('user_id', $this->user()->id);
                }),
            ],
            'notes' => ['nullable', 'string', 'max:1000'],
        ];
    }
}
