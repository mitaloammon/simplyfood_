<?php

namespace App\Infrastructure\Repositories;

use App\Domains\Order\Order;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

class OrderRepository
{
    public function __construct(protected Order $model) {}

    private function baseQuery()
    {
        return $this->model->newQuery()->with(['customer', 'user', 'items.product', 'delivery.driver', 'paymentTransactions', 'timelines.changedBy']);
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

    public function paginateManagementByUser(int $userId, array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        $query = $this->baseQuery()
            ->where('user_id', $userId)
            ->withCount('items');

        if (!empty($filters['order_number'])) {
            $query->whereKey((int) $filters['order_number']);
        }

        if (!empty($filters['customer'])) {
            $query->whereHas('customer', function ($builder) use ($filters) {
                $builder->where('name', 'like', '%' . trim((string) $filters['customer']) . '%');
            });
        }

        if (!empty($filters['operator'])) {
            $query->whereHas('user', function ($builder) use ($filters) {
                $builder->where('name', 'like', '%' . trim((string) $filters['operator']) . '%');
            });
        }

        if (!empty($filters['status'])) {
            $query->where('status', strtoupper((string) $filters['status']));
        }

        if (!empty($filters['order_type'])) {
            $query->where('order_type', strtoupper((string) $filters['order_type']));
        }

        if (!empty($filters['date'])) {
            $query->whereDate('created_at', $filters['date']);
        }

        if (isset($filters['value_min']) && $filters['value_min'] !== '') {
            $query->where('total', '>=', (float) $filters['value_min']);
        }

        if (isset($filters['value_max']) && $filters['value_max'] !== '') {
            $query->where('total', '<=', (float) $filters['value_max']);
        }

        if (isset($filters['value']) && $filters['value'] !== '') {
            $query->where('total', (float) $filters['value']);
        }

        return $query->orderByDesc('created_at')->paginate($perPage);
    }

    public function findByUser(int|string $id, int $userId): ?Order
    {
        return $this->baseQuery()->where('user_id', $userId)->whereKey($id)->first();
    }

    public function getDashboardAggregatesByUser(int $userId): array
    {
        $activeStatuses = ['WAITING_PAYMENT', 'PAID', 'PREPARING', 'OUT_FOR_DELIVERY'];

        $bindings = implode(',', array_fill(0, count($activeStatuses), '?'));

        $result = $this->model
            ->newQuery()
            ->selectRaw(
                "
                SUM(CASE WHEN status IN ({$bindings}) THEN 1 ELSE 0 END) AS active_orders,
                COALESCE(SUM(CASE WHEN status <> 'CANCELLED' THEN total ELSE 0 END), 0) AS revenue_total,
                COALESCE(AVG(CASE WHEN status <> 'CANCELLED' THEN total END), 0) AS average_ticket
                ",
                $activeStatuses
            )
            ->where('user_id', $userId)
            ->whereNull('deleted_at')
            ->first();

        return [
            'active_orders' => (int) ($result?->active_orders ?? 0),
            'revenue_total' => (float) ($result?->revenue_total ?? 0),
            'average_ticket' => (float) ($result?->average_ticket ?? 0),
        ];
    }
}
