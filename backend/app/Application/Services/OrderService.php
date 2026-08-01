<?php

namespace App\Application\Services;

use App\Domains\Customer\Customer;
use App\Domains\Order\Order;
use App\Domains\Order\OrderItem;
use App\Infrastructure\Repositories\OrderItemRepository;
use App\Infrastructure\Repositories\OrderRepository;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class OrderService extends BaseService
{
    protected string $modelClass = Order::class;

    public function __construct(
        protected OrderRepository $repository,
        protected OrderItemRepository $orderItemRepository
    ) {
    }

    /**
     * Create a new order with items and delivery records.
     */
    public function post(array $data): Model
    {
        $userId = isset($data['user_id']) ? (int) $data['user_id'] : null;

        if (!$userId) {
            throw new InvalidArgumentException('User context is required to create an order.');
        }

        return $this->postByUser($userId, $data);
    }

    public function postByUser(int $userId, array $data): Order
    {
        $customer = Customer::query()
            ->where('id', $data['customer_id'])
            ->where('user_id', $userId)
            ->first();

        if (!$customer) {
            throw new InvalidArgumentException('Customer not found for authenticated user.');
        }

        return DB::transaction(function () use ($data, $userId): Order {
            $order = $this->repository->create([
                'user_id' => $userId,
                'customer_id' => $data['customer_id'],
                'status' => $data['status'] ?? 'WAITING_PAYMENT',
                'total' => $this->calculateTotal($data['items']),
            ]);

            foreach ($data['items'] as $item) {
                $this->orderItemRepository->create([
                    'order_id' => $order->id,
                    'product_id' => $item['product_id'],
                    'quantity' => $item['quantity'],
                    'price' => $item['price'],
                ]);
            }

            \App\Domains\Delivery\Delivery::create([
                'order_id' => $order->id,
                'status' => 'PENDING',
                'delivery_fee' => 5.00,
            ]);

            return $this->findByUserOrFail($order->id, $userId);
        });
    }

    public function getByUser(int $userId, array $filters = []): Collection
    {
        return $this->repository->getByUser($userId, $filters);
    }

    public function findByUserOrFail(int|string $id, int $userId): Order
    {
        $order = $this->repository->findByUser($id, $userId);

        if (!$order) {
            throw (new ModelNotFoundException())->setModel(Order::class, [(string) $id]);
        }

        return $order;
    }

    public function updateByUser(int|string $id, int $userId, array $data): Order
    {
        $order = $this->findByUserOrFail($id, $userId);

        if (isset($data['customer_id'])) {
            $customer = Customer::query()
                ->where('id', $data['customer_id'])
                ->where('user_id', $userId)
                ->first();

            if (!$customer) {
                throw new InvalidArgumentException('Customer not found for authenticated user.');
            }
        }

        return DB::transaction(function () use ($order, $data): Order {
            $order->update([
                'customer_id' => $data['customer_id'] ?? $order->customer_id,
                'status' => $data['status'] ?? $order->status,
                'total' => isset($data['items']) ? $this->calculateTotal($data['items']) : $order->total,
            ]);

            if (isset($data['items'])) {
                OrderItem::query()->where('order_id', $order->id)->delete();

                foreach ($data['items'] as $item) {
                    $this->orderItemRepository->create([
                        'order_id' => $order->id,
                        'product_id' => $item['product_id'],
                        'quantity' => $item['quantity'],
                        'price' => $item['price'],
                    ]);
                }
            }

            return $order->fresh(['customer', 'items.product', 'delivery.driver', 'paymentTransactions']);
        });
    }

    public function deleteByUser(int|string $id, int $userId): bool
    {
        $order = $this->findByUserOrFail($id, $userId);
        return (bool) $order->delete();
    }

    public function updateStatusByUser(int|string $id, int $userId, string $newStatus): Order
    {
        $order = $this->findByUserOrFail($id, $userId);
        return $this->applyStatusTransition($order, $newStatus);
    }

    /**
     * Update the status of an order after validating the state transition.
     */
    public function updateStatus(int|string $id, string $newStatus): Order
    {
        $order = $this->find($id);
        return $this->applyStatusTransition($order, $newStatus);
    }

    private function applyStatusTransition(Order $order, string $newStatus): Order
    {
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

    private function calculateTotal(array $items): float
    {
        $total = collect($items)->sum(function (array $item): float {
            return (float) $item['price'] * (int) $item['quantity'];
        });

        return round($total, 2);
    }
}
