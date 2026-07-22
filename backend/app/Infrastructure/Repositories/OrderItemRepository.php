<?php

namespace App\Infrastructure\Repositories;

use App\Domains\Order\OrderItem;

class OrderItemRepository
{
    public function __construct(protected OrderItem $model) {}

    public function create(array $data)
    {
        return $this->model->create($data);
    }

    public function getByOrder(int|string $orderId)
    {
        return $this->model->where('order_id', $orderId)->get();
    }
}
