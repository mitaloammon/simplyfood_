<?php

namespace App\Http\Requests\Product;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateProductRequest extends FormRequest
{
    public function rules(): array
    {
        $establishmentId = $this->user()->establishment_id;

        return [
            'category_id' => [
                'sometimes',
                'nullable',
                'uuid',
                Rule::exists('categories', 'id')
                    ->where('establishment_id', $establishmentId)
                    ->whereNull('deleted_at'),
            ],
            'name' => ['sometimes', 'required', 'string', 'max:150'],
            'description' => ['sometimes', 'nullable', 'string'],
            'price' => ['sometimes', 'required', 'numeric', 'min:0', 'decimal:0,2'],
            'cost_price' => ['sometimes', 'numeric', 'min:0', 'decimal:0,2'],
            'sku' => [
                'sometimes',
                'nullable',
                'string',
                'max:50',
                Rule::unique('products', 'sku')
                    ->where('establishment_id', $establishmentId)
                    ->ignore($this->route('product')),
            ],
            'is_available' => ['sometimes', 'boolean'],
            'preparation_time_minutes' => ['sometimes', 'integer', 'min:0'],
        ];
    }
}
