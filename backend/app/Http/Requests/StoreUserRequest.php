<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreUserRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:users,email,' . ($this->route('id') ?? 'NULL'),
            'password' => ($this->isMethod('post') ? 'required' : 'nullable') . '|string|min:6',
            'role' => 'required|string|in:ADMIN,OPERATOR,MANAGER,DELIVERY',
        ];
    }
}
