<?php

namespace App\Http\Requests\Customer;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateCustomerRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'name' => ['sometimes', 'required', 'string', 'min:2', 'max:150'],
            'phone' => [
                'sometimes',
                'nullable',
                'string',
                'min:8',
                'max:20',
                Rule::unique('customers', 'phone')
                    ->where('establishment_id', $this->user()->establishment_id)
                    ->ignore($this->route('customer')),
            ],
            'email' => ['sometimes', 'nullable', 'email', 'max:255'],
            'document' => ['sometimes', 'nullable', 'string', 'max:30'],
            'address' => ['sometimes', 'nullable', 'string'],
        ];
    }
}
