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

        $customersCount = Customer::query()->count();

        $ordersTodayCount = Order::query()
            ->whereDate('created_at', $today)
            ->count();

        $revenueToday = (float) Order::query()
            ->whereDate('created_at', $today)
            ->sum('total');

        $avgDeliveryMinutes = $this->calculateAverageDeliveryMinutes($today);

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

    private function calculateAverageDeliveryMinutes(Carbon $today): int
    {
        $deliveries = Delivery::query()
            ->select(['created_at', 'delivered_at'])
            ->whereDate('delivered_at', $today)
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
