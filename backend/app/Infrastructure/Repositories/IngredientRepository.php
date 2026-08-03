<?php

namespace App\Infrastructure\Repositories;

use App\Domains\Recipe\Ingredient;
use Illuminate\Support\Collection;

class IngredientRepository
{
    public function __construct(private readonly Ingredient $model)
    {
    }

    public function create(array $data): Ingredient
    {
        return $this->model->create($data);
    }

    public function getByUser(int $userId): Collection
    {
        return $this->model
            ->newQuery()
            ->where('user_id', $userId)
            ->whereNull('deleted_at')
            ->orderBy('name')
            ->get();
    }

    public function findByUser(int|string $id, int $userId): ?Ingredient
    {
        return $this->model
            ->newQuery()
            ->whereKey($id)
            ->where('user_id', $userId)
            ->whereNull('deleted_at')
            ->first();
    }
}
