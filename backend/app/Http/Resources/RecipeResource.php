<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class RecipeResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'user_id' => $this->user_id,
            'product_id' => $this->product_id,
            'name' => $this->name,
            'yield_quantity' => (float) $this->yield_quantity,
            'active' => (bool) $this->active,
            'product' => $this->whenLoaded('product', fn () => [
                'id' => $this->product?->id,
                'nome' => $this->product?->nome,
            ]),
            'items' => $this->whenLoaded('items', fn () => $this->items->map(function ($item) {
                return [
                    'id' => $item->id,
                    'ingredient_id' => $item->ingredient_id,
                    'quantity' => (float) $item->quantity,
                    'ingredient' => $item->ingredient ? [
                        'id' => $item->ingredient->id,
                        'name' => $item->ingredient->name,
                        'unit' => $item->ingredient->unit,
                    ] : null,
                ];
            })->values()),
            'created_at' => optional($this->created_at)?->toISOString(),
            'updated_at' => optional($this->updated_at)?->toISOString(),
        ];
    }
}
