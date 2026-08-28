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
            'name' => $this->name,
            'description' => $this->description,
            'price' => $this->price,
            'cost_price' => $this->cost_price,
            'sku' => $this->sku,
            'is_available' => $this->is_available,
            'preparation_time_minutes' => $this->preparation_time_minutes,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
