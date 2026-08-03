<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CommandResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'user_id' => $this->user_id,
            'table_id' => $this->table_id,
            'customer_id' => $this->customer_id,
            'status' => $this->status,
            'subtotal' => (float) $this->subtotal,
            'total' => (float) $this->total,
            'notes' => $this->notes,
            'opened_at' => optional($this->opened_at)?->toISOString(),
            'closed_at' => optional($this->closed_at)?->toISOString(),
            'table' => $this->whenLoaded('table', fn () => [
                'id' => $this->table?->id,
                'number' => $this->table?->number,
                'status' => $this->table?->status,
            ]),
            'customer' => $this->whenLoaded('customer', fn () => [
                'id' => $this->customer?->id,
                'name' => $this->customer?->name,
            ]),
            'created_at' => optional($this->created_at)?->toISOString(),
            'updated_at' => optional($this->updated_at)?->toISOString(),
        ];
    }
}
