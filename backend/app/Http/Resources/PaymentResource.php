<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PaymentResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'order_id' => $this->order_id,
            'cash_register_shift_id' => $this->cash_register_shift_id,
            'payment_method' => $this->payment_method,
            'amount' => $this->amount,
            'status' => $this->status,
            'transaction_code' => $this->transaction_code,
            'created_at' => $this->created_at,
        ];
    }
}
