<?php

namespace App\Infrastructure\Repositories;

use App\Domains\Order\Order;

class OrderRepository
{
    public function __construct(protected Order $model) {}

    public function create(array $data)
    {
        return $this->model->create($data);
    }

    public function all()
    {
        return $this->model->with(['customer', 'items.product', 'delivery.driver', 'paymentTransactions'])->get();
    }

    public function find(int|string $id)
    {
        return $this->model->with(['customer', 'items.product', 'delivery.driver', 'paymentTransactions'])->find($id);
    }

    public function update(int|string $id, array $data)
    {
        $record = $this->find($id);
        if ($record) {
            $record->update($data);
        }
        return $record;
    }

    public function delete(int|string $id)
    {
        $record = $this->find($id);
        return $record ? $record->delete() : false;
    }
}
