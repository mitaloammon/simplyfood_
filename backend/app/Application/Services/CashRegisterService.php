<?php

namespace App\Application\Services;

use App\Domains\CashRegister\CashClosing;
use App\Domains\CashRegister\CashRegister;
use App\Domains\CashRegister\CashTransaction;
use App\Infrastructure\Repositories\CashClosingRepository;
use App\Infrastructure\Repositories\CashRegisterRepository;
use App\Infrastructure\Repositories\CashTransactionRepository;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class CashRegisterService
{
    public function __construct(
        private readonly CashRegisterRepository $cashRegisterRepository,
        private readonly CashTransactionRepository $cashTransactionRepository,
        private readonly CashClosingRepository $cashClosingRepository,
    ) {
    }

    public function open(int $userId, float $openingBalance): CashRegister
    {
        $current = $this->cashRegisterRepository->getCurrentByUser($userId);
        if ($current) {
            throw new InvalidArgumentException('O operador já possui um caixa aberto.');
        }

        return $this->cashRegisterRepository->create([
            'user_id' => $userId,
            'status' => 'OPEN',
            'opening_balance' => $openingBalance,
            'current_balance' => $openingBalance,
            'opened_at' => now(),
        ]);
    }

    public function current(int $userId): ?CashRegister
    {
        return $this->cashRegisterRepository->getCurrentByUser($userId)?->load(['transactions', 'closings']);
    }

    public function history(int $userId): Collection
    {
        return $this->cashRegisterRepository->getHistoryByUser($userId);
    }

    public function transaction(int $userId, string $type, float $amount, ?string $description = null): CashTransaction
    {
        $register = $this->cashRegisterRepository->getCurrentByUser($userId);
        if (!$register) {
            throw new InvalidArgumentException('Nao existe caixa aberto para este operador.');
        }

        if ($amount <= 0) {
            throw new InvalidArgumentException('Valor da movimentacao deve ser maior que zero.');
        }

        $normalizedType = strtoupper($type);
        if (in_array($normalizedType, ['SANGRIA', 'ESTORNO'], true)) {
            $newBalance = (float) $register->current_balance - $amount;
        } else {
            $newBalance = (float) $register->current_balance + $amount;
        }

        return DB::transaction(function () use ($register, $userId, $normalizedType, $amount, $description, $newBalance) {
            $transaction = $this->cashTransactionRepository->create([
                'cash_register_id' => $register->id,
                'user_id' => $userId,
                'type' => $normalizedType,
                'amount' => $amount,
                'description' => $description,
                'metadata' => [
                    'logged_at' => now()->toISOString(),
                ],
            ]);

            $register->update([
                'current_balance' => $newBalance,
            ]);

            return $transaction;
        });
    }

    public function close(int $userId, float $declaredAmount, bool $blindClosing, ?string $notes = null): CashClosing
    {
        $register = $this->cashRegisterRepository->getCurrentByUser($userId);
        if (!$register) {
            throw new InvalidArgumentException('Nao existe caixa aberto para fechamento.');
        }

        $expectedAmount = (float) $register->current_balance;
        $difference = $declaredAmount - $expectedAmount;

        return DB::transaction(function () use ($register, $userId, $expectedAmount, $declaredAmount, $difference, $blindClosing, $notes) {
            $closing = $this->cashClosingRepository->create([
                'cash_register_id' => $register->id,
                'user_id' => $userId,
                'expected_amount' => $expectedAmount,
                'declared_amount' => $declaredAmount,
                'difference' => $difference,
                'blind_closing' => $blindClosing,
                'notes' => $notes,
                'closed_at' => now(),
            ]);

            $register->update([
                'status' => 'CLOSED',
                'closed_at' => now(),
            ]);

            return $closing;
        });
    }
}
