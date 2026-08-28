<?php

namespace App\Http\Requests\Table;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateTableRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'number' => [
                'sometimes',
                'required',
                'integer',
                'min:1',
                Rule::unique('tables', 'number')
                    ->where('establishment_id', $this->user()->establishment_id)
                    ->ignore($this->route('table')),
            ],
            'capacity' => ['sometimes', 'integer', 'min:1'],
        ];
    }
}
