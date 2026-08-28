<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class DiningTable extends Model
{
    use HasUuids, SoftDeletes;

    protected $table = 'tables';

    protected $fillable = [
        'establishment_id',
        'number',
        'capacity',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'number' => 'integer',
            'capacity' => 'integer',
        ];
    }

    public function commands(): HasMany
    {
        return $this->hasMany(Command::class, 'table_id');
    }
}
