<?php

namespace App\Infrastructure\Repositories;

use App\Domains\CashRegister\CashTransaction;

class CashTransactionRepository
{
    public function __construct(private readonly CashTransaction $model)
    {
    }

    public function create(array $data): CashTransaction
    {
        return $this->model->create($data);
    }
}
