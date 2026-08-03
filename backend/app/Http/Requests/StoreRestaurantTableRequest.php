<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreRestaurantTableRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'number' => ['required', 'integer', 'min:1'],
            'capacity' => ['nullable', 'integer', 'min:1'],
            'location' => ['nullable', 'string', 'max:120'],
            'status' => ['nullable', 'string', 'in:LIVRE,RESERVADA,OCUPADA,PRODUCAO,FECHANDO_CONTA,INDISPONIVEL'],
            'description' => ['nullable', 'string', 'max:255'],
        ];
    }
}
