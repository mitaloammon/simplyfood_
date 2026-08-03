<?php

namespace App\Infrastructure\Repositories;

use App\Domains\CashRegister\CashClosing;

class CashClosingRepository
{
    public function __construct(private readonly CashClosing $model)
    {
    }

    public function create(array $data): CashClosing
    {
        return $this->model->create($data);
    }
}
