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
            'user_id' => $this->user_id,
            'customer_id' => $this->customer_id,
            'customer' => new CustomerResource($this->whenLoaded('customer')),
            'operator' => $this->whenLoaded('user', fn () => [
                'id' => $this->user?->id,
                'name' => $this->user?->name,
                'role' => $this->user?->role,
            ]),
            'status' => $this->status,
            'order_type' => $this->order_type,
            'total' => (float) $this->total,
            'discount' => (float) ($this->discount ?? 0),
            'surcharge' => (float) ($this->surcharge ?? 0),
            'notes' => $this->notes,
            'items_count' => (int) ($this->items_count ?? $this->items?->count() ?? 0),
            'financial_summary' => [
                'items_count' => (int) ($this->items?->sum('quantity') ?? 0),
                'subtotal' => (float) ($this->items?->sum(fn ($item) => (float) $item->price * (int) $item->quantity) ?? 0),
                'discount' => (float) ($this->discount ?? 0),
                'surcharge' => (float) ($this->surcharge ?? 0),
                'total' => (float) $this->total,
            ],
            'items' => OrderItemResource::collection($this->whenLoaded('items')),
            'delivery' => new DeliveryResource($this->whenLoaded('delivery')),
            'payment_transactions' => PaymentTransactionResource::collection($this->whenLoaded('paymentTransactions')),
            'timeline' => OrderTimelineResource::collection($this->whenLoaded('timelines')),
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
        ];
    }
}
