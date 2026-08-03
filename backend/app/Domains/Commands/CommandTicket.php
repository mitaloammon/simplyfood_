<?php

namespace App\Domains\Commands;

use App\Domains\Auth\User\User;
use App\Domains\Customer\Customer;
use App\Domains\Tables\RestaurantTable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class CommandTicket extends Model
{
    use SoftDeletes;

    protected $table = 'commands';

    protected $fillable = [
        'user_id',
        'table_id',
        'customer_id',
        'status',
        'subtotal',
        'total',
        'notes',
        'opened_at',
        'closed_at',
    ];

    protected function casts(): array
    {
        return [
            'user_id' => 'integer',
            'table_id' => 'integer',
            'customer_id' => 'integer',
            'subtotal' => 'decimal:2',
            'total' => 'decimal:2',
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

    public function table(): BelongsTo
    {
        return $this->belongsTo(RestaurantTable::class, 'table_id');
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }
}
