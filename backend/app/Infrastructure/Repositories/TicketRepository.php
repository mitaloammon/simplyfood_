<?php

namespace App\Infrastructure\Repositories;

use App\Domains\Ticket\Ticket;

class TicketRepository
{
    public function __construct(protected Ticket $model) {}

    public function create(array $data)
    {
        return $this->model->create($data);
    }

    public function all()
    {
        return $this->model->with('customer')->get();
    }

    public function find(int|string $id)
    {
        return $this->model->with('customer')->find($id);
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
