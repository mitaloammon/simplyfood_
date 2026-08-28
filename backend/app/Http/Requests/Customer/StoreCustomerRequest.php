<?php

namespace App\Http\Requests\Customer;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreCustomerRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'min:2', 'max:150'],
            'phone' => [
                'nullable',
                'string',
                'min:8',
                'max:20',
                Rule::unique('customers', 'phone')
                    ->where('establishment_id', $this->user()->establishment_id),
            ],
            'email' => ['nullable', 'email', 'max:255'],
            'document' => ['nullable', 'string', 'max:30'],
            'address' => ['nullable', 'string'],
        ];
    }
}
