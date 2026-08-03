<?php

namespace App\Infrastructure\Repositories;

use App\Domains\Customer\Customer;
use Illuminate\Support\Collection;

class CustomerRepository
{
    public function __construct(protected Customer $model) {}

    public function create(array $data): Customer
    {
        return $this->model->create($data);
    }

    public function findByWhatsapp(string $whatsapp): ?Customer
    {
        return $this->model->where('whatsapp', $whatsapp)->first();
    }

    public function findByWhatsappForUser(string $whatsapp, int $userId): ?Customer
    {
        return $this->model
            ->where('user_id', $userId)
            ->where('whatsapp', $whatsapp)
            ->first();
    }

    public function all(): \Illuminate\Database\Eloquent\Collection
    {
        return $this->model->all();
    }

    public function find(int|string $id): ?Customer
    {
        return $this->model->find($id);
    }

    public function update(int|string $id, array $data): ?Customer
    {
        $record = $this->find($id);
        if ($record) {
            $record->update($data);
        }
        return $record;
    }

    public function delete(int|string $id): bool
    {
        $record = $this->find($id);
        return $record ? $record->delete() : false;
    }

    public function getByUser(int $userId, array $filters = []): Collection
    {
        $query = $this->model->newQuery()->where('user_id', $userId);

        foreach ($filters as $key => $value) {
            if ($value !== null && $value !== '' && in_array($key, $this->model->getFillable(), true)) {
                $query->where($key, $value);
            }
        }

        return $query->get();
    }

    public function findByUser(int|string $id, int $userId): ?Customer
    {
        return $this->model
            ->newQuery()
            ->where('user_id', $userId)
            ->whereKey($id)
            ->first();
    }

    public function countActiveByUser(int $userId): int
    {
        return $this->model
            ->newQuery()
            ->where('user_id', $userId)
            ->whereNull('deleted_at')
            ->count();
    }
}
