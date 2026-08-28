<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class CashRegister extends Model
{
    use HasUuids, SoftDeletes;

    protected $fillable = [
        'establishment_id',
        'name',
        'location',
        'is_active',
    ];

    protected function casts(): array
    {
        return ['is_active' => 'boolean'];
    }

    public function shifts(): HasMany
    {
        return $this->hasMany(CashRegisterShift::class);
    }
}
