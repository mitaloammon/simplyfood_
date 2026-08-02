<?php

namespace App\Infrastructure\Repositories;

use App\Domains\Order\OrderTimeline;
use Illuminate\Support\Collection;

class OrderTimelineRepository
{
    public function __construct(protected OrderTimeline $model)
    {
    }

    public function create(array $data): OrderTimeline
    {
        return $this->model->create($data);
    }

    public function getByOrder(int|string $orderId): Collection
    {
        return $this->model
            ->newQuery()
            ->with('changedBy')
            ->where('order_id', $orderId)
            ->orderBy('created_at')
            ->get();
    }
}
