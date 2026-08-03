<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CashRegisterResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'user_id' => $this->user_id,
            'status' => $this->status,
            'opening_balance' => (float) $this->opening_balance,
            'current_balance' => (float) $this->current_balance,
            'opened_at' => optional($this->opened_at)?->toISOString(),
            'closed_at' => optional($this->closed_at)?->toISOString(),
            'transactions' => CashTransactionResource::collection($this->whenLoaded('transactions')),
            'closings' => CashClosingResource::collection($this->whenLoaded('closings')),
            'created_at' => optional($this->created_at)?->toISOString(),
            'updated_at' => optional($this->updated_at)?->toISOString(),
        ];
    }
}
