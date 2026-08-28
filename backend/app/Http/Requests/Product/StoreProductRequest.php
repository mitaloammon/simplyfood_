<?php

namespace App\Http\Requests\Product;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreProductRequest extends FormRequest
{
    public function rules(): array
    {
        $establishmentId = $this->user()->establishment_id;

        return [
            'category_id' => [
                'nullable',
                'uuid',
                Rule::exists('categories', 'id')
                    ->where('establishment_id', $establishmentId)
                    ->whereNull('deleted_at'),
            ],
            'name' => ['required', 'string', 'max:150'],
            'description' => ['nullable', 'string'],
            'price' => ['required', 'numeric', 'min:0', 'decimal:0,2'],
            'cost_price' => ['sometimes', 'numeric', 'min:0', 'decimal:0,2'],
            'sku' => [
                'nullable',
                'string',
                'max:50',
                Rule::unique('products', 'sku')->where('establishment_id', $establishmentId),
            ],
            'is_available' => ['sometimes', 'boolean'],
            'preparation_time_minutes' => ['sometimes', 'integer', 'min:0'],
        ];
    }
}
