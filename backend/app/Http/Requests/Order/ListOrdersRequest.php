<?php

namespace App\Http\Requests\Order;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ListOrdersRequest extends FormRequest
{
    public function rules(): array
    {
        $establishmentId = $this->user()->establishment_id;

        return [
            'status' => ['sometimes', Rule::in([
                'OPEN', 'IN_PREPARATION', 'READY', 'DELIVERED', 'CLOSED', 'CANCELLED',
            ])],
            'table_id' => [
                'sometimes', 'uuid',
                Rule::exists('tables', 'id')
                    ->where('establishment_id', $establishmentId)
                    ->whereNull('deleted_at'),
            ],
            'command_id' => [
                'sometimes', 'uuid',
                Rule::exists('commands', 'id')
                    ->where('establishment_id', $establishmentId)
                    ->whereNull('deleted_at'),
            ],
            'page' => ['sometimes', 'integer', 'min:1'],
            'per_page' => ['sometimes', 'integer', 'min:1', 'max:100'],
        ];
    }
}
