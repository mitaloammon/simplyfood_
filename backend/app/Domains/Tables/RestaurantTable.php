<?php

namespace App\Domains\Tables;

use App\Domains\Auth\User\User;
use App\Domains\Commands\CommandTicket;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class RestaurantTable extends Model
{
    use SoftDeletes;

    protected $table = 'restaurant_tables';

    protected $fillable = [
        'user_id',
        'number',
        'capacity',
        'location',
        'status',
        'description',
    ];

    protected function casts(): array
    {
        return [
            'user_id' => 'integer',
            'number' => 'integer',
            'capacity' => 'integer',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
            'deleted_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function commands(): HasMany
    {
        return $this->hasMany(CommandTicket::class, 'table_id');
    }
}
