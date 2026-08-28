<?php

namespace App\Http\Requests\Table;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateTableStatusRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'status' => [
                'required',
                Rule::in(['FREE', 'OCCUPIED', 'RESERVED', 'BILLING']),
            ],
        ];
    }
}
