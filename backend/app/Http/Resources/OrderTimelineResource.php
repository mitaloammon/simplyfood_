<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class OrderTimelineResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'event_type' => $this->event_type,
            'title' => $this->title,
            'description' => $this->description,
            'metadata' => $this->metadata,
            'changed_by' => $this->changed_by,
            'operator' => $this->changedBy ? [
                'id' => $this->changedBy->id,
                'name' => $this->changedBy->name,
                'role' => $this->changedBy->role,
            ] : null,
            'created_at' => $this->created_at?->toIso8601String(),
        ];
    }
}
