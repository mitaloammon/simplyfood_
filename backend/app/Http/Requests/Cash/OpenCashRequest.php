<?php

namespace App\Http\Requests\Cash;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class OpenCashRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'cash_register_id' => [
                'required',
                'uuid',
                Rule::exists('cash_registers', 'id')
                    ->where('establishment_id', $this->user()->establishment_id)
                    ->where('is_active', true)
                    ->whereNull('deleted_at'),
            ],
            'opening_balance' => [
                'required',
                'numeric',
                'min:0',
                'max:99999999.99',
                'decimal:0,2',
            ],
        ];
    }
}
