<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Command extends Model
{
    use HasUuids, SoftDeletes;

    protected $fillable = [
        'establishment_id',
        'table_id',
        'code',
        'status',
    ];

    public function table(): BelongsTo
    {
        return $this->belongsTo(DiningTable::class, 'table_id')->withTrashed();
    }
}
