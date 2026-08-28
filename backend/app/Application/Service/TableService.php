<?php

namespace App\Application\Service;

use App\Exceptions\BusinessConflictException;
use App\Models\DiningTable;
use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

class TableService
{
    public function paginate(User $user, int $perPage): LengthAwarePaginator
    {
        return DiningTable::query()
            ->where('establishment_id', $user->establishment_id)
            ->orderBy('number')
            ->paginate($perPage);
    }

    public function create(User $user, array $data): DiningTable
    {
        return DiningTable::query()->create([
            ...$data,
            'establishment_id' => $user->establishment_id,
            'status' => 'FREE',
        ]);
    }

    public function find(User $user, string $id): DiningTable
    {
        return DiningTable::query()
            ->where('establishment_id', $user->establishment_id)
            ->findOrFail($id);
    }

    public function update(User $user, string $id, array $data): DiningTable
    {
        $table = $this->find($user, $id);
        $table->update($data);

        return $table->refresh();
    }

    public function updateStatus(User $user, string $id, string $status): DiningTable
    {
        return DB::transaction(function () use ($user, $id, $status) {
            $table = DiningTable::query()
                ->where('establishment_id', $user->establishment_id)
                ->lockForUpdate()
                ->findOrFail($id);

            if ($status === 'FREE' && $table->status !== 'FREE') {
                $hasOpenCommand = DB::table('commands')
                    ->where('establishment_id', $user->establishment_id)
                    ->where('table_id', $table->id)
                    ->where('status', 'OPEN')
                    ->whereNull('deleted_at')
                    ->lockForUpdate()
                    ->exists();

                $hasActiveOrder = DB::table('orders')
                    ->where('establishment_id', $user->establishment_id)
                    ->where('table_id', $table->id)
                    ->whereNotIn('status', ['CLOSED', 'CANCELLED'])
                    ->whereNull('deleted_at')
                    ->lockForUpdate()
                    ->exists();

                if ($hasOpenCommand || $hasActiveOrder) {
                    throw new BusinessConflictException(
                        'Não é possível liberar a mesa com comanda ou pedido ativo'
                    );
                }
            }

            $table->update(['status' => $status]);

            return $table->refresh();
        });
    }
}
