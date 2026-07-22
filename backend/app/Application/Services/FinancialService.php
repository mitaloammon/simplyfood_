<?php

namespace App\Application\Services;

use App\Domains\Order\Order;
use App\Domains\Payment\PaymentTransaction;
use Illuminate\Support\Facades\DB;

class FinancialService
{
    /**
     * Get financial metrics for dashboard.
     */
    public function getMetrics(): array
    {
        $totalRevenue = Order::where('status', 'PAID')
            ->orWhere('status', 'DELIVERED')
            ->orWhere('status', 'PREPARING')
            ->orWhere('status', 'OUT_FOR_DELIVERY')
            ->sum('total');

        $pendingRevenue = Order::where('status', 'WAITING_PAYMENT')
            ->sum('total');

        $ordersCount = Order::count();
        $deliveredCount = Order::where('status', 'DELIVERED')->count();
        
        // Breakdowns by payment method
        $paymentBreakdown = PaymentTransaction::select('payment_method', DB::raw('SUM(amount) as total'))
            ->where('status', 'APPROVED')
            ->groupBy('payment_method')
            ->get()
            ->toArray();

        // Volume progression (grouped by date)
        $dailyRevenue = Order::select(DB::raw('DATE(created_at) as date'), DB::raw('SUM(total) as total'))
            ->whereIn('status', ['PAID', 'DELIVERED', 'PREPARING', 'OUT_FOR_DELIVERY'])
            ->groupBy('date')
            ->orderBy('date', 'asc')
            ->get()
            ->toArray();

        return [
            'total_revenue' => (float) $totalRevenue,
            'pending_revenue' => (float) $pendingRevenue,
            'total_orders' => $ordersCount,
            'completed_orders' => $deliveredCount,
            'payment_breakdown' => $paymentBreakdown,
            'daily_revenue' => $dailyRevenue
        ];
    }
}
