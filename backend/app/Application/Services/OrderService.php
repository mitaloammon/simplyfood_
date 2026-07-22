<?php

namespace App\Application\Services;

use App\Domains\Order\Order;
use InvalidArgumentException;

class OrderService extends BaseService
{
    protected string $modelClass = Order::class;

    /**
     * Create a new order with items and delivery records.
     */
    public function post(array $data): \Illuminate\Database\Eloquent\Model
    {
        return \Illuminate\Support\Facades\DB::transaction(function () use ($data) {
            // Create Order
            $order = Order::create([
                'customer_id' => $data['customer_id'],
                'status' => $data['status'] ?? 'WAITING_PAYMENT',
                'total' => $data['total']
            ]);

            // Create Items
            foreach ($data['items'] as $item) {
                \App\Domains\Order\OrderItem::create([
                    'order_id' => $order->id,
                    'product_id' => $item['product_id'],
                    'quantity' => $item['quantity'],
                    'price' => $item['price']
                ]);
            }

            // Create default Delivery record
            \App\Domains\Delivery\Delivery::create([
                'order_id' => $order->id,
                'status' => 'PENDING',
                'delivery_fee' => 5.00
            ]);

            return $order->load(['customer', 'items.product', 'delivery']);
        });
    }

    /**
     * Update the status of an order after validating the state transition.
     */
    public function updateStatus(int|string $id, string $newStatus): Order
    {
        $order = $this->find($id);
        $currentStatus = strtoupper($order->status);
        $newStatus = strtoupper($newStatus);

        if ($currentStatus === $newStatus) {
            return $order;
        }

        // Define valid transitions
        $validTransitions = [
            'WAITING_PAYMENT' => ['PAID', 'CANCELLED'],
            'PAID' => ['PREPARING', 'CANCELLED'],
            'PREPARING' => ['OUT_FOR_DELIVERY', 'CANCELLED'],
            'OUT_FOR_DELIVERY' => ['DELIVERED', 'CANCELLED'],
        ];

        // Terminal states (DELIVERED, CANCELLED) cannot transition to anything
        if ($currentStatus === 'DELIVERED' || $currentStatus === 'CANCELLED') {
            throw new InvalidArgumentException("Cannot transition from a terminal state ({$currentStatus}) to {$newStatus}.");
        }

        // Validate state transitions
        if (!isset($validTransitions[$currentStatus]) || !in_array($newStatus, $validTransitions[$currentStatus])) {
            throw new InvalidArgumentException("Invalid status transition: Cannot change order status from {$currentStatus} to {$newStatus}.");
        }

        $order->status = $newStatus;
        $order->save();

        return $order;
    }
}
