<?php

namespace App\Http\Requests\Cash;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CashMovementRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'type' => ['required', Rule::in(['BLEED', 'SUPPLEMENT'])],
            'amount' => [
                'required',
                'numeric',
                'gt:0',
                'max:99999999.99',
                'decimal:0,2',
            ],
            'description' => ['nullable', 'string', 'max:255'],
        ];
    }
}
