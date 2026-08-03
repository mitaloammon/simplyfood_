<?php

namespace App\Infrastructure\Repositories;

use App\Domains\Recipe\Recipe;
use Illuminate\Support\Collection;

class RecipeRepository
{
    public function __construct(private readonly Recipe $model)
    {
    }

    public function create(array $data): Recipe
    {
        return $this->model->create($data);
    }

    public function getByUser(int $userId): Collection
    {
        return $this->model
            ->newQuery()
            ->with(['product', 'items.ingredient'])
            ->where('user_id', $userId)
            ->whereNull('deleted_at')
            ->orderByDesc('id')
            ->get();
    }

    public function findByUser(int|string $id, int $userId): ?Recipe
    {
        return $this->model
            ->newQuery()
            ->with(['product', 'items.ingredient'])
            ->whereKey($id)
            ->where('user_id', $userId)
            ->whereNull('deleted_at')
            ->first();
    }
}
