<?php

namespace App\Application\Services;

use App\Domains\Auth\User\User;
use App\Domains\Customer\Customer;
use App\Domains\Delivery\Delivery;
use App\Domains\Order\Order;
use Carbon\Carbon;

class DashboardService
{
    public function buildUserDashboard(User $user): array
    {
        $today = Carbon::today();
        $dayStart = $today->copy()->startOfDay();
        $dayEnd = $today->copy()->endOfDay();

        $customersCount = Customer::query()->where('user_id', $user->id)->count();

        $ordersTodayCount = Order::query()
            ->where('user_id', $user->id)
            ->where('deleted_at', null)
            ->whereBetween('created_at', [$dayStart, $dayEnd])
            ->count();

        $revenueToday = (float) Order::query()
            ->where('user_id', $user->id)
            ->where('deleted_at', null)
            ->whereBetween('created_at', [$dayStart, $dayEnd])
            ->sum('total');

        $avgDeliveryMinutes = $this->calculateAverageDeliveryMinutes($dayStart, $dayEnd, $user->id);

        return [
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'role' => $user->role,
            ],
            'metrics' => [
                [
                    'key' => 'customers',
                    'title' => 'Clientes Cadastrados',
                    'value' => (string) $customersCount,
                    'description' => 'Base total de clientes ativos no sistema.',
                    'icon' => 'users',
                ],
                [
                    'key' => 'orders_today',
                    'title' => 'Pedidos Hoje',
                    'value' => (string) $ordersTodayCount,
                    'description' => 'Total de pedidos criados no dia atual.',
                    'icon' => 'shopping-bag',
                ],
                [
                    'key' => 'revenue_today',
                    'title' => 'Faturamento Diário',
                    'value' => 'R$ ' . number_format($revenueToday, 2, ',', '.'),
                    'description' => 'Soma de pedidos do dia corrente.',
                    'icon' => 'banknotes',
                ],
                [
                    'key' => 'delivery_avg',
                    'title' => 'Tempo Médio Entrega',
                    'value' => $avgDeliveryMinutes . ' min',
                    'description' => 'Média diária entre criação e entrega concluída.',
                    'icon' => 'clock',
                ],
            ],
        ];
    }

    private function calculateAverageDeliveryMinutes(Carbon $dayStart, Carbon $dayEnd, int $userId): int
    {
        $deliveries = Delivery::query()
            ->select(['created_at', 'delivered_at'])
            ->whereHas('order', function ($query) use ($userId): void {
                $query->where('user_id', $userId);
            })
            ->whereBetween('delivered_at', [$dayStart, $dayEnd])
            ->whereNotNull('delivered_at')
            ->get();

        if ($deliveries->isEmpty()) {
            return 0;
        }

        $average = $deliveries
            ->map(function (Delivery $delivery): int {
                return (int) $delivery->created_at?->diffInMinutes($delivery->delivered_at);
            })
            ->avg();

        return (int) round($average ?? 0);
    }
}
