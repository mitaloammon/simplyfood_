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
            'preco' => (float) $this->preco,
            'imagem' => $this->imagem,
            'tempo_preparo' => $this->tempo_preparo,
            'ativo' => (bool) $this->ativo,
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
        ];
    }
}
