<?php

namespace App\Http\Requests\Category;

use Illuminate\Foundation\Http\FormRequest;

class UpdateCategoryRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'name' => ['sometimes', 'required', 'string', 'max:100'],
            'sort_order' => ['sometimes', 'integer', 'min:0'],
            'active' => ['sometimes', 'boolean'],
        ];
    }
}
