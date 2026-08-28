<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CashMovementResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'cash_register_shift_id' => $this->cash_register_shift_id,
            'user_id' => $this->user_id,
            'type' => $this->type,
            'amount' => $this->amount,
            'description' => $this->description,
            'created_at' => $this->created_at,
        ];
    }
}
