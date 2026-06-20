<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreCardRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'brand' => ['required', 'string', 'in:Visa,Mastercard,Amex,Discover,GCash,Maya,GrabPay,Coins.ph,BPI,BDO,Metrobank,UnionBank,Cash,Other'],
            'last_four' => ['required', 'string', 'regex:/^\d{4}$/'],
            'expiration_month' => ['required', 'integer', 'min:1', 'max:12'],
            'expiration_year' => ['required', 'integer', 'min:'.date('Y'), 'max:2099'],
            'color' => ['required', 'string', 'regex:/^#[0-9A-Fa-f]{6}$/'],
            'is_active' => ['nullable', 'boolean'],
        ];
    }

    public function messages(): array
    {
        return [
            'last_four.regex' => 'The last four digits must be exactly 4 numeric characters.',
            'color.regex' => 'The card color must be a valid hex color code (e.g., #FFFFFF).',
        ];
    }
}
