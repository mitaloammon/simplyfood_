<?php

namespace App\Application\Dashboard;

use App\Models\CashRegisterShift;
use App\Models\DiningTable;
use App\Models\Order;
use App\Models\Payment;
use App\Models\User;

class DashboardMetricsService
{
    public function get(User $user): array
    {
        $establishmentId = $user->establishment_id;

        return [
            'open_orders' => Order::query()
                ->where('establishment_id', $establishmentId)
                ->whereNotIn('status', ['CLOSED', 'CANCELLED'])
                ->count(),
            'occupied_tables' => DiningTable::query()
                ->where('establishment_id', $establishmentId)
                ->where('status', 'OCCUPIED')
                ->count(),
            'today_revenue' => (float) Payment::query()
                ->where('establishment_id', $establishmentId)
                ->where('status', 'CONFIRMED')
                ->whereDate('created_at', today())
                ->sum('amount'),
            'open_shift' => CashRegisterShift::query()
                ->where('establishment_id', $establishmentId)
                ->where('status', 'OPEN')
                ->exists(),
        ];
    }
}
