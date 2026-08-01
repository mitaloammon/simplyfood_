<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreOrderRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $isUpdate = in_array($this->method(), ['PUT', 'PATCH'], true);

        return [
            'customer_id' => $isUpdate ? 'sometimes|integer|exists:customers,id' : 'required|integer|exists:customers,id',
            'items' => $isUpdate ? 'sometimes|array|min:1' : 'required|array|min:1',
            'items.*.product_id' => 'required_with:items|integer|exists:products,id',
            'items.*.quantity' => 'required_with:items|integer|min:1',
            'items.*.price' => 'required_with:items|numeric|min:0',
            'status' => 'nullable|string|max:50',
            'total' => 'nullable|numeric|min:0'
        ];
    }
}
