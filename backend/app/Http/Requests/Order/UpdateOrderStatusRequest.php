<?php

namespace App\Http\Requests\Order;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateOrderStatusRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'status' => ['required', Rule::in([
                'IN_PREPARATION', 'READY', 'DELIVERED', 'CLOSED', 'CANCELLED',
            ])],
        ];
    }
}
