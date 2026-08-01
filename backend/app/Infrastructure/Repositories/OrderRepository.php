<?php

namespace App\Infrastructure\Repositories;

use App\Domains\Order\Order;
use Illuminate\Support\Collection;

class OrderRepository
{
    public function __construct(protected Order $model) {}

    private function baseQuery()
    {
        return $this->model->newQuery()->with(['customer', 'items.product', 'delivery.driver', 'paymentTransactions']);
    }

    public function create(array $data): Order
    {
        return $this->model->create($data);
    }

    public function all(): Collection
    {
        return $this->baseQuery()->get();
    }

    public function find(int|string $id): ?Order
    {
        return $this->baseQuery()->find($id);
    }

    public function update(int|string $id, array $data): ?Order
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
        $query = $this->baseQuery()->where('user_id', $userId);

        foreach ($filters as $key => $value) {
            if ($value !== null && $value !== '' && in_array($key, ['status', 'customer_id'], true)) {
                $query->where($key, $value);
            }
        }

        return $query->orderByDesc('created_at')->get();
    }

    public function findByUser(int|string $id, int $userId): ?Order
    {
        return $this->baseQuery()->where('user_id', $userId)->whereKey($id)->first();
    }
}
