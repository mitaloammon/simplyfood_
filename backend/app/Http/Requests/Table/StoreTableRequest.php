<?php

namespace App\Http\Requests\Table;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreTableRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'number' => [
                'required',
                'integer',
                'min:1',
                Rule::unique('tables', 'number')
                    ->where('establishment_id', $this->user()->establishment_id),
            ],
            'capacity' => ['sometimes', 'integer', 'min:1'],
        ];
    }
}
