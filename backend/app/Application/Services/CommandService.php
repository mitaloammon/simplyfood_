<?php

namespace App\Application\Services;

use App\Domains\Commands\CommandTicket;
use App\Infrastructure\Repositories\CommandRepository;
use App\Infrastructure\Repositories\RestaurantTableRepository;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Collection;
use InvalidArgumentException;

class CommandService
{
    public function __construct(
        private readonly CommandRepository $commandRepository,
        private readonly RestaurantTableRepository $tableRepository,
    ) {
    }

    public function open(int $userId, array $data): CommandTicket
    {
        $table = $this->tableRepository->findByUser((int) $data['table_id'], $userId);

        if (!$table) {
            throw new InvalidArgumentException('Mesa nao encontrada para o operador autenticado.');
        }

        if (!in_array($table->status, ['LIVRE', 'RESERVADA'], true)) {
            throw new InvalidArgumentException('Mesa indisponivel para abertura de comanda.');
        }

        $table->update(['status' => 'OCUPADA']);

        return $this->commandRepository->create([
            'user_id' => $userId,
            'table_id' => $table->id,
            'customer_id' => $data['customer_id'] ?? null,
            'status' => 'ABERTA',
            'subtotal' => (float) ($data['subtotal'] ?? 0),
            'total' => (float) ($data['total'] ?? 0),
            'notes' => $data['notes'] ?? null,
            'opened_at' => now(),
        ])->load(['table', 'customer']);
    }

    public function listByUser(int $userId): Collection
    {
        return $this->commandRepository->getByUser($userId);
    }

    public function updateStatus(int $userId, int|string $id, string $status): CommandTicket
    {
        $command = $this->commandRepository->findByUser($id, $userId);

        if (!$command) {
            throw (new ModelNotFoundException())->setModel(CommandTicket::class, [(string) $id]);
        }

        $normalizedStatus = strtoupper($status);

        $command->update([
            'status' => $normalizedStatus,
            'closed_at' => $normalizedStatus === 'FECHADA' ? now() : null,
        ]);

        if ($normalizedStatus === 'FECHADA') {
            $command->table?->update(['status' => 'LIVRE']);
        }

        return $command->fresh(['table', 'customer']);
    }
}
