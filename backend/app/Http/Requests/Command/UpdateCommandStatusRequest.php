<?php

namespace App\Http\Requests\Command;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateCommandStatusRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'status' => [
                'required',
                Rule::in(['OPEN', 'CLOSED', 'BLOCKED']),
            ],
        ];
    }
}
