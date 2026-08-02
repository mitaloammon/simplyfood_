<?php

namespace App\Domains\Product;

use App\Domains\Auth\User\User;
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
        'preco_venda',
        'custo',
        'unidade',
        'codigo_barras',
        'imagem',
        'tempo_preparo',
        'ativo',
        'controla_estoque',
        'produzido_cozinha',
        'delivery',
        'balcao',
        'mesa',
        'retirada',
        'created_by',
        'updated_by',
    ];

    protected function casts(): array
    {
        return [
            'category_id' => 'integer',
            'preco' => 'decimal:2',
            'preco_venda' => 'decimal:2',
            'custo' => 'decimal:2',
            'unidade' => 'string',
            'codigo_barras' => 'string',
            'tempo_preparo' => 'integer',
            'ativo' => 'boolean',
            'controla_estoque' => 'boolean',
            'produzido_cozinha' => 'boolean',
            'delivery' => 'boolean',
            'balcao' => 'boolean',
            'mesa' => 'boolean',
            'retirada' => 'boolean',
            'created_by' => 'integer',
            'updated_by' => 'integer',
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

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updatedBy()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }
}
