<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class AddGoalFundsRequest extends FormRequest
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
            'amount' => ['required', 'integer', 'min:1', 'max:9999999900'],
            'operation' => ['required', 'string', 'in:add,withdraw'],
        ];
    }
}
