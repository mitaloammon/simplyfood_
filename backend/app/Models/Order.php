<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Order extends Model
{
    use HasUuids, SoftDeletes;

    protected $fillable = [
        'establishment_id', 'cash_register_shift_id', 'waiter_id', 'customer_id',
        'table_id', 'command_id', 'order_type', 'status', 'subtotal', 'discount',
        'total_amount',
    ];

    protected function casts(): array
    {
        return [
            'subtotal' => 'decimal:2',
            'discount' => 'decimal:2',
            'total_amount' => 'decimal:2',
        ];
    }

    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class)->withTrashed();
    }

    public function table(): BelongsTo
    {
        return $this->belongsTo(DiningTable::class, 'table_id')->withTrashed();
    }

    public function command(): BelongsTo
    {
        return $this->belongsTo(Command::class)->withTrashed();
    }

    public function shift(): BelongsTo
    {
        return $this->belongsTo(CashRegisterShift::class, 'cash_register_shift_id')->withTrashed();
    }
}
