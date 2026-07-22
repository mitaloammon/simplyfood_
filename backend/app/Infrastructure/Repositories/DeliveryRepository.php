<?php

namespace App\Infrastructure\Repositories;

use App\Domains\Delivery\Delivery;

class DeliveryRepository
{
    public function __construct(protected Delivery $model) {}

    public function create(array $data)
    {
        return $this->model->create($data);
    }

    public function all()
    {
        return $this->model->with(['order.customer', 'driver'])->get();
    }

    public function find(int|string $id)
    {
        return $this->model->with(['order.customer', 'driver'])->find($id);
    }

    public function update(int|string $id, array $data)
    {
        $record = $this->find($id);
        if ($record) {
            $record->update($data);
        }
        return $record;
    }
}
