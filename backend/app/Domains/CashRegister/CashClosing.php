<?php

namespace App\Domains\CashRegister;

use App\Domains\Auth\User\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class CashClosing extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'cash_register_id',
        'user_id',
        'expected_amount',
        'declared_amount',
        'difference',
        'blind_closing',
        'notes',
        'closed_at',
    ];

    protected function casts(): array
    {
        return [
            'cash_register_id' => 'integer',
            'user_id' => 'integer',
            'expected_amount' => 'decimal:2',
            'declared_amount' => 'decimal:2',
            'difference' => 'decimal:2',
            'blind_closing' => 'boolean',
            'closed_at' => 'datetime',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
            'deleted_at' => 'datetime',
        ];
    }

    public function cashRegister(): BelongsTo
    {
        return $this->belongsTo(CashRegister::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
