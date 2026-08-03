<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateRestaurantTableStatusRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'status' => ['required', 'string', 'in:LIVRE,RESERVADA,OCUPADA,PRODUCAO,FECHANDO_CONTA,INDISPONIVEL'],
        ];
    }
}
