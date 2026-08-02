<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProductResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'category_id' => $this->category_id,
            'category' => new CategoryResource($this->whenLoaded('category')),
            'nome' => $this->nome,
            'descricao' => $this->descricao,
            'preco' => (float) ($this->preco_venda ?? $this->preco),
            'preco_venda' => (float) ($this->preco_venda ?? $this->preco),
            'custo' => $this->custo !== null ? (float) $this->custo : null,
            'unidade' => $this->unidade,
            'codigo_barras' => $this->codigo_barras,
            'imagem' => $this->imagem,
            'tempo_preparo' => $this->tempo_preparo,
            'ativo' => (bool) $this->ativo,
            'controla_estoque' => (bool) $this->controla_estoque,
            'produzido_cozinha' => (bool) $this->produzido_cozinha,
            'delivery' => (bool) $this->delivery,
            'balcao' => (bool) $this->balcao,
            'mesa' => (bool) $this->mesa,
            'retirada' => (bool) $this->retirada,
            'created_by' => $this->created_by,
            'updated_by' => $this->updated_by,
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
        ];
    }
}
