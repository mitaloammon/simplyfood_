<?php

namespace App\Http\Requests\Product;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ListProductsRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'category_id' => [
                'sometimes',
                'uuid',
                Rule::exists('categories', 'id')
                    ->where('establishment_id', $this->user()->establishment_id)
                    ->whereNull('deleted_at'),
            ],
            'is_available' => ['sometimes', 'boolean'],
            'q' => ['sometimes', 'string', 'max:150'],
            'page' => ['sometimes', 'integer', 'min:1'],
            'per_page' => ['sometimes', 'integer', 'min:1', 'max:100'],
        ];
    }
}
