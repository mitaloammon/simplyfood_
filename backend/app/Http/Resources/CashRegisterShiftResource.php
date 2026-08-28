<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CashRegisterShiftResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'cash_register' => [
                'id' => $this->cashRegister->id,
                'name' => $this->cashRegister->name,
                'location' => $this->cashRegister->location,
            ],
            'user_id' => $this->user_id,
            'opening_balance' => $this->opening_balance,
            'closing_balance' => $this->closing_balance,
            'opened_at' => $this->opened_at,
            'closed_at' => $this->closed_at,
            'status' => $this->status,
            'notes' => $this->notes,
            'movements' => CashMovementResource::collection($this->whenLoaded('movements')),
        ];
    }
}
