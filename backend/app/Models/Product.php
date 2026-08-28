<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Product extends Model
{
    use HasUuids, SoftDeletes;

    protected $fillable = [
        'establishment_id',
        'category_id',
        'name',
        'description',
        'price',
        'cost_price',
        'sku',
        'is_available',
        'preparation_time_minutes',
    ];

    protected function casts(): array
    {
        return [
            'price' => 'decimal:2',
            'cost_price' => 'decimal:2',
            'is_available' => 'boolean',
            'preparation_time_minutes' => 'integer',
        ];
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }
}
