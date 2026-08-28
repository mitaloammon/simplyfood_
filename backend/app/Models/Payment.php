<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Payment extends Model
{
    use HasUuids, SoftDeletes;

    public const UPDATED_AT = null;

    protected $fillable = [
        'establishment_id',
        'order_id',
        'cash_register_shift_id',
        'payment_method',
        'amount',
        'status',
        'transaction_code',
    ];

    protected function casts(): array
    {
        return ['amount' => 'decimal:2'];
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class)->withTrashed();
    }
}
