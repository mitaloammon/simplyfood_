<?php

namespace App\Domains\Product;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Builder;

class Product extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'category_id',
        'nome',
        'descricao',
        'preco',
        'imagem',
        'tempo_preparo',
        'ativo'
    ];

    protected function casts(): array
    {
        return [
            'category_id' => 'integer',
            'preco' => 'decimal:2',
            'tempo_preparo' => 'integer',
            'ativo' => 'boolean',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
            'deleted_at' => 'datetime',
        ];
    }

    // Scope for active products
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('ativo', true);
    }

    // Relationships
    public function category()
    {
        return $this->belongsTo(Category::class);
    }
}
