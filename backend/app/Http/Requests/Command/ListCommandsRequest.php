<?php

namespace App\Http\Requests\Command;

use Illuminate\Foundation\Http\FormRequest;

class ListCommandsRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'page' => ['sometimes', 'integer', 'min:1'],
            'per_page' => ['sometimes', 'integer', 'min:1', 'max:100'],
        ];
    }
}
