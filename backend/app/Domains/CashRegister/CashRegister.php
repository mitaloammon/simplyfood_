<?php

namespace App\Domains\CashRegister;

use App\Domains\Auth\User\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class CashRegister extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'user_id',
        'status',
        'opening_balance',
        'current_balance',
        'opened_at',
        'closed_at',
    ];

    protected function casts(): array
    {
        return [
            'user_id' => 'integer',
            'opening_balance' => 'decimal:2',
            'current_balance' => 'decimal:2',
            'opened_at' => 'datetime',
            'closed_at' => 'datetime',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
            'deleted_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function transactions(): HasMany
    {
        return $this->hasMany(CashTransaction::class);
    }

    public function closings(): HasMany
    {
        return $this->hasMany(CashClosing::class);
    }
}
