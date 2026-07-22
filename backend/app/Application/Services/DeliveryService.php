<?php

namespace App\Application\Services;

use App\Domains\Delivery\Delivery;
use App\Domains\Order\Order;
use Exception;

class DeliveryService extends BaseService
{
    protected string $modelClass = Delivery::class;

    /**
     * Assign a driver to a delivery.
     */
    public function assignDriver(int|string $id, int|string $driverId): Delivery
    {
        $delivery = $this->find($id);
        $delivery->driver_id = $driverId;
        $delivery->status = 'ACCEPTED';
        $delivery->save();

        return $delivery;
    }

    /**
     * Update delivery status.
     */
    public function updateDeliveryStatus(int|string $id, string $status): Delivery
    {
        $delivery = $this->find($id);
        $delivery->status = strtoupper($status);

        if ($delivery->status === 'DELIVERED') {
            $delivery->delivered_at = now();
            // Update order status as well
            $order = $delivery->order;
            $order->status = 'DELIVERED';
            $order->save();
        }

        $delivery->save();

        return $delivery;
    }
}
