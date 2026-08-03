<?php

namespace App\Domains\CashRegister;

use App\Domains\Auth\User\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class CashTransaction extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'cash_register_id',
        'user_id',
        'type',
        'amount',
        'description',
        'metadata',
    ];

    protected function casts(): array
    {
        return [
            'cash_register_id' => 'integer',
            'user_id' => 'integer',
            'amount' => 'decimal:2',
            'metadata' => 'array',
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
