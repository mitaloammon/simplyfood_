<?php

namespace App\Domains\Order;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Domains\Customer\Customer;
use App\Domains\Auth\User\User;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Order extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'user_id',
        'customer_id',
        'status', // WAITING_PAYMENT, PAID, PREPARING, OUT_FOR_DELIVERY, DELIVERED, CANCELLED
        'total'
    ];

    protected function casts(): array
    {
        return [
            'total' => 'decimal:2',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
            'deleted_at' => 'datetime',
        ];
    }

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function items()
    {
        return $this->hasMany(OrderItem::class);
    }

    public function paymentTransactions()
    {
        return $this->hasMany(\App\Domains\Payment\PaymentTransaction::class);
    }

    public function delivery()
    {
        return $this->hasOne(\App\Domains\Delivery\Delivery::class);
    }
}
