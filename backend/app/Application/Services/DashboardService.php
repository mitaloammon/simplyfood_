<?php

namespace App\Application\Services;

use App\Domains\Auth\User\User;
use App\Infrastructure\Repositories\CustomerRepository;
use App\Infrastructure\Repositories\OrderRepository;

class DashboardService
{
    public function __construct(
        private readonly CustomerRepository $customerRepository,
        private readonly OrderRepository $orderRepository,
    ) {
    }

    public function buildUserDashboard(User $user): array
    {
        $customersCount = $this->customerRepository->countActiveByUser((int) $user->id);
        $orderAggregates = $this->orderRepository->getDashboardAggregatesByUser((int) $user->id);

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
                    'key' => 'orders_active',
                    'title' => 'Pedidos Ativos',
                    'value' => (string) $orderAggregates['active_orders'],
                    'description' => 'Pedidos em andamento: aguardando pagamento, pagos, em preparo ou em entrega.',
                    'icon' => 'shopping-bag',
                ],
                [
                    'key' => 'revenue_total',
                    'title' => 'Faturamento Total',
                    'value' => 'R$ ' . number_format($orderAggregates['revenue_total'], 2, ',', '.'),
                    'description' => 'Soma dos pedidos não cancelados do usuário autenticado.',
                    'icon' => 'banknotes',
                ],
                [
                    'key' => 'average_ticket',
                    'title' => 'Ticket Médio',
                    'value' => 'R$ ' . number_format($orderAggregates['average_ticket'], 2, ',', '.'),
                    'description' => 'Valor médio por pedido não cancelado do usuário autenticado.',
                    'icon' => 'chart-bar',
                ],
            ],
        ];
    }
}
