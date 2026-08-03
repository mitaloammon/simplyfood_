<?php

namespace App\Infrastructure\Repositories;

use App\Domains\Commands\CommandTicket;
use Illuminate\Support\Collection;

class CommandRepository
{
    public function __construct(private readonly CommandTicket $model)
    {
    }

    public function create(array $data): CommandTicket
    {
        return $this->model->create($data);
    }

    public function getByUser(int $userId): Collection
    {
        return $this->model
            ->newQuery()
            ->with(['table', 'customer'])
            ->where('user_id', $userId)
            ->whereNull('deleted_at')
            ->orderByDesc('opened_at')
            ->get();
    }

    public function findByUser(int|string $id, int $userId): ?CommandTicket
    {
        return $this->model
            ->newQuery()
            ->with(['table', 'customer'])
            ->whereKey($id)
            ->where('user_id', $userId)
            ->whereNull('deleted_at')
            ->first();
    }
}
