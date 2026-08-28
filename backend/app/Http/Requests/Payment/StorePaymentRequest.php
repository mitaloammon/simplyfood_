<?php

namespace App\Http\Requests\Payment;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StorePaymentRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'payment_method' => [
                'required',
                Rule::in(['CASH', 'CREDIT_CARD', 'DEBIT_CARD', 'PIX', 'VOUCHER']),
            ],
            'amount' => [
                'required',
                'numeric',
                'gt:0',
                'max:99999999.99',
                'decimal:0,2',
            ],
        ];
    }
}
