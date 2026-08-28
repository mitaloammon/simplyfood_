<?php

namespace App\Application\Service;

use App\Exceptions\BusinessConflictException;
use App\Models\Command;
use App\Models\DiningTable;
use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

class CommandService
{
    public function paginate(User $user, int $perPage): LengthAwarePaginator
    {
        return Command::query()
            ->where('establishment_id', $user->establishment_id)
            ->with('table')
            ->orderByDesc('created_at')
            ->paginate($perPage);
    }

    public function open(User $user, array $data): Command
    {
        return DB::transaction(function () use ($user, $data) {
            $table = DiningTable::query()
                ->where('establishment_id', $user->establishment_id)
                ->lockForUpdate()
                ->findOrFail($data['table_id']);

            if ($table->status === 'BILLING') {
                throw new BusinessConflictException(
                    'Não é possível abrir comanda em mesa em fechamento'
                );
            }

            $command = Command::query()->create([
                'establishment_id' => $user->establishment_id,
                'table_id' => $table->id,
                'code' => $data['code'],
                'status' => 'OPEN',
            ]);

            if ($table->status !== 'OCCUPIED') {
                $table->update(['status' => 'OCCUPIED']);
            }

            return $command->load('table');
        });
    }

    public function updateStatus(
        User $user,
        string $id,
        string $status
    ): Command {
        return DB::transaction(function () use ($user, $id, $status) {
            $command = Command::query()
                ->where('establishment_id', $user->establishment_id)
                ->lockForUpdate()
                ->findOrFail($id);

            if ($command->status === $status) {
                return $command->load('table');
            }

            if ($status === 'OPEN') {
                $this->reopen($user, $command);
            }

            if ($status === 'CLOSED') {
                $hasBlockingOrder = DB::table('orders')
                    ->where('establishment_id', $user->establishment_id)
                    ->where('command_id', $command->id)
                    ->whereIn('status', ['OPEN', 'IN_PREPARATION'])
                    ->whereNull('deleted_at')
                    ->lockForUpdate()
                    ->exists();

                if ($hasBlockingOrder) {
                    throw new BusinessConflictException(
                        'Não é possível fechar a comanda com pedido aberto ou em preparação'
                    );
                }
            }

            $command->update(['status' => $status]);

            return $command->refresh()->load('table');
        });
    }

    private function reopen(User $user, Command $command): void
    {
        if ($command->status !== 'FREE' || ! $command->table_id) {
            throw new BusinessConflictException(
                'Somente uma comanda FREE vinculada a uma mesa pode ser aberta'
            );
        }

        $table = DiningTable::query()
            ->where('establishment_id', $user->establishment_id)
            ->lockForUpdate()
            ->findOrFail($command->table_id);

        if ($table->status === 'BILLING') {
            throw new BusinessConflictException(
                'Não é possível abrir comanda em mesa em fechamento'
            );
        }

        if ($table->status !== 'OCCUPIED') {
            $table->update(['status' => 'OCCUPIED']);
        }
    }
}
