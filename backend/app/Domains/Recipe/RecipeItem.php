<?php

namespace App\Domains\Recipe;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class RecipeItem extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'recipe_id',
        'ingredient_id',
        'quantity',
    ];

    protected function casts(): array
    {
        return [
            'recipe_id' => 'integer',
            'ingredient_id' => 'integer',
            'quantity' => 'decimal:3',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
            'deleted_at' => 'datetime',
        ];
    }

    public function recipe(): BelongsTo
    {
        return $this->belongsTo(Recipe::class);
    }

    public function ingredient(): BelongsTo
    {
        return $this->belongsTo(Ingredient::class);
    }
}
