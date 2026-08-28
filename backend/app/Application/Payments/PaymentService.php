<?php

namespace App\Application\Payments;

use App\Exceptions\BusinessConflictException;
use App\Models\CashRegisterShift;
use App\Models\Order;
use App\Models\Payment;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class PaymentService
{
    public function create(User $user, string $orderId, array $data): array
    {
        return DB::transaction(function () use ($user, $orderId, $data) {
            $order = Order::query()
                ->where('establishment_id', $user->establishment_id)
                ->lockForUpdate()
                ->findOrFail($orderId);

            if (in_array($order->status, ['CLOSED', 'CANCELLED'], true)) {
                throw new BusinessConflictException(
                    'Não é possível pagar um pedido encerrado'
                );
            }

            $shift = CashRegisterShift::query()
                ->where('establishment_id', $user->establishment_id)
                ->where('status', 'OPEN')
                ->lockForUpdate()
                ->find($order->cash_register_shift_id);

            if (! $shift) {
                throw new BusinessConflictException(
                    'Pagamento exige o turno de caixa do pedido aberto'
                );
            }

            $payment = Payment::query()->create([
                'establishment_id' => $user->establishment_id,
                'order_id' => $order->id,
                'cash_register_shift_id' => $shift->id,
                'payment_method' => $data['payment_method'],
                'amount' => $data['amount'],
                'status' => 'CONFIRMED',
                'transaction_code' => null,
            ]);

            $paidAmount = Payment::query()
                ->where('establishment_id', $user->establishment_id)
                ->where('order_id', $order->id)
                ->where('status', 'CONFIRMED')
                ->lockForUpdate()
                ->sum('amount');

            $remaining = max(
                0,
                (float) $order->total_amount - (float) $paidAmount
            );

            return [
                'payment' => $payment,
                'paid_amount' => number_format((float) $paidAmount, 2, '.', ''),
                'remaining_amount' => number_format($remaining, 2, '.', ''),
                'fully_paid' => (float) $paidAmount >= (float) $order->total_amount,
            ];
        });
    }
}
