<?php

namespace App\Domains\Payment;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Domains\Order\Order;

class PaymentTransaction extends Model
{
    protected $fillable = [
        'order_id',
        'gateway',
        'transaction_id',
        'status',
        'amount',
        'payment_method'
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
        ];
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }
}
