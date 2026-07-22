<?php

namespace App\Infrastructure\Repositories;

use App\Domains\Customer\Address\Address;

class AddressRepository
{
    public function __construct(protected Address $model) {}

    public function create(array $data)
    {
        return $this->model->create($data);
    }

    public function find(int|string $id)
    {
        return $this->model->find($id);
    }

    public function getByCustomer(int|string $customerId)
    {
        return $this->model->where('customer_id', $customerId)->get();
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
