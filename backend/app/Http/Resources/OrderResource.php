<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class OrderResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'cash_register_shift_id' => $this->cash_register_shift_id,
            'waiter_id' => $this->waiter_id,
            'customer_id' => $this->customer_id,
            'table_id' => $this->table_id,
            'command_id' => $this->command_id,
            'order_type' => $this->order_type,
            'status' => $this->status,
            'subtotal' => $this->subtotal,
            'discount' => $this->discount,
            'total_amount' => $this->total_amount,
            'items' => OrderItemResource::collection($this->whenLoaded('items')),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
