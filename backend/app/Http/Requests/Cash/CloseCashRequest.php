<?php

namespace App\Http\Requests\Cash;

use Illuminate\Foundation\Http\FormRequest;

class CloseCashRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'closing_balance' => [
                'required',
                'numeric',
                'min:0',
                'max:99999999.99',
                'decimal:0,2',
            ],
            'notes' => ['nullable', 'string'],
        ];
    }
}
