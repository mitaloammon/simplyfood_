<?php

namespace App\Http\Requests\Order;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class AddOrderItemRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'product_id' => [
                'required', 'uuid',
                Rule::exists('products', 'id')
                    ->where('establishment_id', $this->user()->establishment_id)
                    ->where('is_available', true)
                    ->whereNull('deleted_at'),
            ],
            'quantity' => ['required', 'integer', 'min:1'],
            'notes' => ['nullable', 'string', 'max:255'],
        ];
    }
}
