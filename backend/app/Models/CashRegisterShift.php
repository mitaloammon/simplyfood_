<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class CashRegisterShift extends Model
{
    use HasUuids, SoftDeletes;

    protected $fillable = [
        'establishment_id',
        'cash_register_id',
        'user_id',
        'opening_balance',
        'closing_balance',
        'opened_at',
        'closed_at',
        'status',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'opening_balance' => 'decimal:2',
            'closing_balance' => 'decimal:2',
            'opened_at' => 'datetime',
            'closed_at' => 'datetime',
        ];
    }

    public function cashRegister(): BelongsTo
    {
        return $this->belongsTo(CashRegister::class)->withTrashed();
    }

    public function movements(): HasMany
    {
        return $this->hasMany(CashMovement::class);
    }
}
