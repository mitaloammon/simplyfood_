<?php

namespace App\Infrastructure\Repositories;

use App\Domains\Recipe\RecipeItem;

class RecipeItemRepository
{
    public function __construct(private readonly RecipeItem $model)
    {
    }

    public function create(array $data): RecipeItem
    {
        return $this->model->create($data);
    }
}
