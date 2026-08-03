<?php

namespace App\Domains\Recipe;

use App\Domains\Auth\User\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Ingredient extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'user_id',
        'name',
        'unit',
        'current_stock',
        'min_stock',
        'active',
    ];

    protected function casts(): array
    {
        return [
            'user_id' => 'integer',
            'current_stock' => 'decimal:3',
            'min_stock' => 'decimal:3',
            'active' => 'boolean',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
            'deleted_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function recipeItems(): HasMany
    {
        return $this->hasMany(RecipeItem::class);
    }
}
