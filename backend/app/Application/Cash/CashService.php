<?php

namespace App\Application\Cash;

use App\Exceptions\BusinessConflictException;
use App\Models\CashMovement;
use App\Models\CashRegister;
use App\Models\CashRegisterShift;
use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

class CashService
{
    public function open(User $user, array $data): CashRegisterShift
    {
        return DB::transaction(function () use ($user, $data) {
            $register = CashRegister::query()
                ->where('establishment_id', $user->establishment_id)
                ->where('is_active', true)
                ->lockForUpdate()
                ->findOrFail($data['cash_register_id']);

            $alreadyOpen = CashRegisterShift::query()
                ->where('establishment_id', $user->establishment_id)
                ->where('cash_register_id', $register->id)
                ->where('status', 'OPEN')
                ->lockForUpdate()
                ->exists();

            if ($alreadyOpen) {
                throw new BusinessConflictException('Este caixa já possui um turno aberto');
            }

            $shift = CashRegisterShift::query()->create([
                'establishment_id' => $user->establishment_id,
                'cash_register_id' => $register->id,
                'user_id' => $user->id,
                'opening_balance' => $data['opening_balance'],
                'opened_at' => now(),
                'status' => 'OPEN',
            ]);

            return $shift->load('cashRegister', 'movements');
        });
    }

    public function current(User $user): ?CashRegisterShift
    {
        return $this->openShiftQuery($user)
            ->with('cashRegister', 'movements')
            ->first();
    }

    public function addMovement(User $user, array $data): CashMovement
    {
        return DB::transaction(function () use ($user, $data) {
            $shift = $this->openShiftQuery($user)
                ->lockForUpdate()
                ->first();

            if (! $shift) {
                throw new BusinessConflictException('Não há turno de caixa aberto');
            }

            return CashMovement::query()->create([
                'establishment_id' => $user->establishment_id,
                'cash_register_shift_id' => $shift->id,
                'user_id' => $user->id,
                'type' => $data['type'],
                'amount' => $data['amount'],
                'description' => $data['description'] ?? null,
            ]);
        });
    }

    public function history(User $user, int $perPage): LengthAwarePaginator
    {
        return CashRegisterShift::query()
            ->where('establishment_id', $user->establishment_id)
            ->with('cashRegister')
            ->orderByDesc('opened_at')
            ->paginate($perPage);
    }

    public function close(User $user, array $data): CashRegisterShift
    {
        return DB::transaction(function () use ($user, $data) {
            $shift = $this->openShiftQuery($user)
                ->lockForUpdate()
                ->first();

            if (! $shift) {
                throw new BusinessConflictException('Não há turno de caixa aberto');
            }

            $hasActiveOrders = DB::table('orders')
                ->where('establishment_id', $user->establishment_id)
                ->where('cash_register_shift_id', $shift->id)
                ->whereNull('deleted_at')
                ->whereNotIn('status', ['CLOSED', 'CANCELLED'])
                ->lockForUpdate()
                ->exists();

            if ($hasActiveOrders) {
                throw new BusinessConflictException(
                    'Não é possível fechar o caixa com pedidos ativos'
                );
            }

            $shift->update([
                'closing_balance' => $data['closing_balance'],
                'closed_at' => now(),
                'status' => 'CLOSED',
                'notes' => $data['notes'] ?? null,
            ]);

            return $shift->refresh()->load('cashRegister', 'movements');
        });
    }

    private function openShiftQuery(User $user)
    {
        return CashRegisterShift::query()
            ->where('establishment_id', $user->establishment_id)
            ->where('status', 'OPEN')
            ->orderByDesc('opened_at');
    }
}
