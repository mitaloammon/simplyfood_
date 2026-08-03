<?php

namespace App\Infrastructure\Repositories;

use App\Domains\CashRegister\CashRegister;
use Illuminate\Support\Collection;

class CashRegisterRepository
{
    public function __construct(private readonly CashRegister $model)
    {
    }

    public function create(array $data): CashRegister
    {
        return $this->model->create($data);
    }

    public function getCurrentByUser(int $userId): ?CashRegister
    {
        return $this->model
            ->newQuery()
            ->where('user_id', $userId)
            ->where('status', 'OPEN')
            ->whereNull('deleted_at')
            ->latest('id')
            ->first();
    }

    public function findByUser(int|string $id, int $userId): ?CashRegister
    {
        return $this->model
            ->newQuery()
            ->whereKey($id)
            ->where('user_id', $userId)
            ->whereNull('deleted_at')
            ->first();
    }

    public function getHistoryByUser(int $userId): Collection
    {
        return $this->model
            ->newQuery()
            ->where('user_id', $userId)
            ->whereNull('deleted_at')
            ->orderByDesc('opened_at')
            ->get();
    }
}
