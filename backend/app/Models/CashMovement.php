<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CashMovement extends Model
{
    use HasUuids;

    public const UPDATED_AT = null;

    protected $fillable = [
        'establishment_id',
        'cash_register_shift_id',
        'user_id',
        'type',
        'amount',
        'description',
    ];

    protected function casts(): array
    {
        return ['amount' => 'decimal:2'];
    }

    public function shift(): BelongsTo
    {
        return $this->belongsTo(CashRegisterShift::class, 'cash_register_shift_id');
    }
}
