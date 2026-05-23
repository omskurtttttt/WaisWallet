<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreTransactionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:255'],
            'amount' => ['required', 'numeric', 'min:0.01', 'max:99999999'],
            'type' => ['required', 'string', Rule::in(['income', 'expense'])],
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
