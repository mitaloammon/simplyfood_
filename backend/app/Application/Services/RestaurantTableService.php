<?php

namespace App\Application\Services;

use App\Domains\Tables\RestaurantTable;
use App\Infrastructure\Repositories\RestaurantTableRepository;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Collection;

class RestaurantTableService
{
    public function __construct(private readonly RestaurantTableRepository $repository)
    {
    }

    public function create(int $userId, array $data): RestaurantTable
    {
        return $this->repository->create([
            'user_id' => $userId,
            'number' => (int) $data['number'],
            'capacity' => (int) ($data['capacity'] ?? 2),
            'location' => $data['location'] ?? null,
            'status' => strtoupper((string) ($data['status'] ?? 'LIVRE')),
            'description' => $data['description'] ?? null,
        ]);
    }

    public function listByUser(int $userId): Collection
    {
        return $this->repository->getByUser($userId);
    }

    public function updateStatus(int $userId, int|string $id, string $status): RestaurantTable
    {
        $table = $this->repository->findByUser($id, $userId);

        if (!$table) {
            throw (new ModelNotFoundException())->setModel(RestaurantTable::class, [(string) $id]);
        }

        $table->update(['status' => strtoupper($status)]);

        return $table;
    }
}
