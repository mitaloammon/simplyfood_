<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CashClosingResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'cash_register_id' => $this->cash_register_id,
            'user_id' => $this->user_id,
            'expected_amount' => (float) $this->expected_amount,
            'declared_amount' => (float) $this->declared_amount,
            'difference' => (float) $this->difference,
            'blind_closing' => (bool) $this->blind_closing,
            'notes' => $this->notes,
            'closed_at' => optional($this->closed_at)?->toISOString(),
            'created_at' => optional($this->created_at)?->toISOString(),
            'updated_at' => optional($this->updated_at)?->toISOString(),
        ];
    }
}
