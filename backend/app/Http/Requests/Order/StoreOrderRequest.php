<?php

namespace App\Http\Requests\Order;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreOrderRequest extends FormRequest
{
    public function rules(): array
    {
        $establishmentId = $this->user()->establishment_id;
        $orderType = $this->input('order_type');

        return [
            'order_type' => ['required', Rule::in(['TABLE', 'COMMAND', 'COUNTER'])],
            'table_id' => [
                Rule::requiredIf(in_array($orderType, ['TABLE', 'COMMAND'], true)),
                Rule::prohibitedIf($orderType === 'COUNTER'),
                'nullable', 'uuid',
                Rule::exists('tables', 'id')
                    ->where('establishment_id', $establishmentId)
                    ->whereNull('deleted_at'),
            ],
            'command_id' => [
                Rule::requiredIf($orderType === 'COMMAND'),
                Rule::prohibitedIf($orderType !== 'COMMAND'),
                'nullable', 'uuid',
                Rule::exists('commands', 'id')
                    ->where('establishment_id', $establishmentId)
                    ->whereNull('deleted_at'),
            ],
            'customer_id' => [
                'nullable', 'uuid',
                Rule::exists('customers', 'id')
                    ->where('establishment_id', $establishmentId)
                    ->whereNull('deleted_at'),
            ],
            'items' => ['required', 'array', 'min:1'],
            'items.*.product_id' => [
                'required', 'uuid',
                Rule::exists('products', 'id')
                    ->where('establishment_id', $establishmentId)
                    ->where('is_available', true)
                    ->whereNull('deleted_at'),
            ],
            'items.*.quantity' => ['required', 'integer', 'min:1'],
            'items.*.notes' => ['nullable', 'string', 'max:255'],
        ];
    }
}
