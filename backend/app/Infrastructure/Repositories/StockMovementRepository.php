<?php

namespace App\Infrastructure\Repositories;

use App\Domains\Stock\StockMovement;

class StockMovementRepository
{
    public function __construct(private readonly StockMovement $model)
    {
    }

    public function create(array $data): StockMovement
    {
        return $this->model->create($data);
    }
}
