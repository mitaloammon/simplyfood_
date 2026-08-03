<?php

namespace App\Infrastructure\Repositories;

use App\Domains\Tables\RestaurantTable;
use Illuminate\Support\Collection;

class RestaurantTableRepository
{
    public function __construct(private readonly RestaurantTable $model)
    {
    }

    public function create(array $data): RestaurantTable
    {
        return $this->model->create($data);
    }

    public function getByUser(int $userId): Collection
    {
        return $this->model
            ->newQuery()
            ->where('user_id', $userId)
            ->whereNull('deleted_at')
            ->orderBy('number')
            ->get();
    }

    public function findByUser(int|string $id, int $userId): ?RestaurantTable
    {
        return $this->model
            ->newQuery()
            ->whereKey($id)
            ->where('user_id', $userId)
            ->whereNull('deleted_at')
            ->first();
    }
}
