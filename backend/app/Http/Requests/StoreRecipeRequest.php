<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreRecipeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'product_id' => ['required', 'integer', 'exists:products,id'],
            'name' => ['required', 'string', 'max:120'],
            'yield_quantity' => ['nullable', 'numeric', 'min:0.001'],
            'active' => ['nullable', 'boolean'],
        ];
    }
}
