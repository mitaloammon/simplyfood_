<?php

namespace App\Http\Requests\Command;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class OpenCommandRequest extends FormRequest
{
    public function rules(): array
    {
        $establishmentId = $this->user()->establishment_id;

        return [
            'code' => [
                'required',
                'string',
                'max:50',
                Rule::unique('commands', 'code')
                    ->where('establishment_id', $establishmentId),
            ],
            'table_id' => [
                'required',
                'uuid',
                Rule::exists('tables', 'id')
                    ->where('establishment_id', $establishmentId)
                    ->whereNull('deleted_at'),
            ],
        ];
    }
}
