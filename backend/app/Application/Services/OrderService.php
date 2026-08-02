<?php

namespace App\Application\Services;

use App\Domains\Customer\Customer;
use App\Domains\Order\Order;
use App\Domains\Order\OrderItem;
use App\Infrastructure\Repositories\OrderTimelineRepository;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
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
        protected OrderItemRepository $orderItemRepository,
        protected OrderTimelineRepository $orderTimelineRepository,
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
        if (!empty($data['customer_id'])) {
            $customer = Customer::query()
                ->where('id', $data['customer_id'])
                ->where('user_id', $userId)
                ->first();

            if (!$customer) {
                throw new InvalidArgumentException('Customer not found for authenticated user.');
            }
        }

        return DB::transaction(function () use ($data, $userId): Order {
            $financialSummary = $this->calculateFinancialSummary(
                $data['items'],
                (float) ($data['discount'] ?? 0),
                (float) ($data['surcharge'] ?? 0)
            );

            $order = $this->repository->create([
                'user_id' => $userId,
                'customer_id' => $data['customer_id'] ?? null,
                'status' => $data['status'] ?? 'WAITING_PAYMENT',
                'order_type' => strtoupper((string) ($data['order_type'] ?? 'BALCAO')),
                'discount' => (float) ($data['discount'] ?? 0),
                'surcharge' => (float) ($data['surcharge'] ?? 0),
                'notes' => $data['notes'] ?? null,
                'total' => $financialSummary['total'],
            ]);

            $this->appendTimelineEvent($order->id, $userId, 'ORDER_CREATED', 'Pedido criado', 'Pedido criado no fluxo operacional.');

            if (!empty($data['customer_id'])) {
                $this->appendTimelineEvent($order->id, $userId, 'CUSTOMER_ASSOCIATED', 'Cliente associado', 'Cliente vinculado ao pedido na criacao.', [
                    'customer_id' => (int) $data['customer_id'],
                ]);
            }

            foreach ($data['items'] as $item) {
                $this->orderItemRepository->create([
                    'order_id' => $order->id,
                    'product_id' => $item['product_id'],
                    'quantity' => $item['quantity'],
                    'price' => $item['price'],
                ]);

                $this->appendTimelineEvent($order->id, $userId, 'ITEM_ADDED', 'Item adicionado', 'Item adicionado ao pedido.', [
                    'product_id' => (int) $item['product_id'],
                    'quantity' => (int) $item['quantity'],
                    'price' => (float) $item['price'],
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

    public function getManagementPageByUser(int $userId, array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        return $this->repository->paginateManagementByUser($userId, $filters, $perPage);
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

        $previousCustomerId = $order->customer_id;
        $previousStatus = $order->status;
        $previousItemKeys = $order->items->map(fn (OrderItem $item) => $this->buildItemKey($item->product_id, $item->quantity, (float) $item->price))->all();

        if (isset($data['customer_id'])) {
            if ($data['customer_id'] !== null) {
                $customer = Customer::query()
                    ->where('id', $data['customer_id'])
                    ->where('user_id', $userId)
                    ->first();

                if (!$customer) {
                    throw new InvalidArgumentException('Customer not found for authenticated user.');
                }
            }
        }

        return DB::transaction(function () use ($order, $data, $userId, $previousCustomerId, $previousStatus, $previousItemKeys): Order {
            $resolvedItems = isset($data['items']) ? $data['items'] : $order->items->map(fn (OrderItem $item) => [
                'product_id' => (int) $item->product_id,
                'quantity' => (int) $item->quantity,
                'price' => (float) $item->price,
            ])->all();

            $financialSummary = $this->calculateFinancialSummary(
                $resolvedItems,
                (float) ($data['discount'] ?? $order->discount ?? 0),
                (float) ($data['surcharge'] ?? $order->surcharge ?? 0)
            );

            $order->update([
                'customer_id' => $data['customer_id'] ?? $order->customer_id,
                'status' => $data['status'] ?? $order->status,
                'order_type' => isset($data['order_type']) ? strtoupper((string) $data['order_type']) : $order->order_type,
                'discount' => isset($data['discount']) ? (float) $data['discount'] : $order->discount,
                'surcharge' => isset($data['surcharge']) ? (float) $data['surcharge'] : $order->surcharge,
                'notes' => array_key_exists('notes', $data) ? $data['notes'] : $order->notes,
                'total' => $financialSummary['total'],
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

                $currentItemKeys = collect($data['items'])
                    ->map(fn (array $item) => $this->buildItemKey((int) $item['product_id'], (int) $item['quantity'], (float) $item['price']))
                    ->all();

                $addedItems = array_values(array_diff($currentItemKeys, $previousItemKeys));
                $removedItems = array_values(array_diff($previousItemKeys, $currentItemKeys));

                foreach ($addedItems as $itemKey) {
                    $this->appendTimelineEvent($order->id, $userId, 'ITEM_ADDED', 'Item adicionado', 'Item incluído durante atualização.', [
                        'item_key' => $itemKey,
                    ]);
                }

                foreach ($removedItems as $itemKey) {
                    $this->appendTimelineEvent($order->id, $userId, 'ITEM_REMOVED', 'Item removido', 'Item removido durante atualização.', [
                        'item_key' => $itemKey,
                    ]);
                }
            }

            if (array_key_exists('customer_id', $data) && $previousCustomerId !== $order->customer_id) {
                if ($order->customer_id) {
                    $this->appendTimelineEvent($order->id, $userId, 'CUSTOMER_ASSOCIATED', 'Cliente associado', 'Cliente vinculado ao pedido.', [
                        'customer_id' => (int) $order->customer_id,
                    ]);
                } else {
                    $this->appendTimelineEvent($order->id, $userId, 'CUSTOMER_UNASSOCIATED', 'Cliente removido', 'Pedido ficou sem cliente associado.');
                }
            }

            if (isset($data['status']) && strtoupper((string) $previousStatus) !== strtoupper((string) $order->status)) {
                $this->appendTimelineEvent($order->id, $userId, 'STATUS_CHANGED', 'Status alterado', 'Status do pedido atualizado.', [
                    'from' => strtoupper((string) $previousStatus),
                    'to' => strtoupper((string) $order->status),
                ]);

                if (strtoupper((string) $order->status) === 'PREPARING') {
                    $this->appendTimelineEvent($order->id, $userId, 'SENT_TO_PRODUCTION', 'Envio para producao', 'Pedido enviado para produção.');
                }

                if (in_array(strtoupper((string) $order->status), ['DELIVERED', 'CANCELLED'], true)) {
                    $this->appendTimelineEvent($order->id, $userId, 'ORDER_FINALIZED', 'Finalizacao', 'Pedido finalizado.');
                }
            }

            return $order->fresh(['customer', 'user', 'items.product', 'delivery.driver', 'paymentTransactions', 'timelines.changedBy']);
        });
    }

    public function associateCustomerByUser(int|string $id, int $userId, int $customerId): Order
    {
        $order = $this->findByUserOrFail($id, $userId);

        $customer = Customer::query()
            ->where('id', $customerId)
            ->where('user_id', $userId)
            ->first();

        if (!$customer) {
            throw new InvalidArgumentException('Customer not found for authenticated user.');
        }

        $order->update(['customer_id' => $customerId]);

        $this->appendTimelineEvent($order->id, $userId, 'CUSTOMER_ASSOCIATED', 'Cliente associado', 'Cliente associado ao pedido no gerenciamento.', [
            'customer_id' => $customerId,
        ]);

        return $order->fresh(['customer', 'user', 'items.product', 'delivery.driver', 'paymentTransactions', 'timelines.changedBy']);
    }

    public function getTimelineByUser(int|string $id, int $userId): Collection
    {
        $order = $this->findByUserOrFail($id, $userId);
        return $this->orderTimelineRepository->getByOrder($order->id);
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

        if ($order->user_id) {
            $this->appendTimelineEvent($order->id, (int) $order->user_id, 'STATUS_CHANGED', 'Status alterado', 'Status do pedido atualizado.', [
                'from' => $currentStatus,
                'to' => $newStatus,
            ]);

            if ($newStatus === 'PREPARING') {
                $this->appendTimelineEvent($order->id, (int) $order->user_id, 'SENT_TO_PRODUCTION', 'Envio para producao', 'Pedido enviado para produção.');
            }

            if (in_array($newStatus, ['DELIVERED', 'CANCELLED'], true)) {
                $this->appendTimelineEvent($order->id, (int) $order->user_id, 'ORDER_FINALIZED', 'Finalizacao', 'Pedido finalizado.');
            }
        }

        return $order->fresh(['customer', 'user', 'items.product', 'delivery.driver', 'paymentTransactions', 'timelines.changedBy']);
    }

    private function calculateTotal(array $items): float
    {
        $total = collect($items)->sum(function (array $item): float {
            return (float) $item['price'] * (int) $item['quantity'];
        });

        return round($total, 2);
    }

    private function calculateFinancialSummary(array $items, float $discount, float $surcharge): array
    {
        $subtotal = $this->calculateTotal($items);
        $total = round(max(0, $subtotal - $discount + $surcharge), 2);

        return [
            'subtotal' => $subtotal,
            'discount' => round($discount, 2),
            'surcharge' => round($surcharge, 2),
            'total' => $total,
            'items_count' => (int) collect($items)->sum(fn (array $item) => (int) ($item['quantity'] ?? 0)),
        ];
    }

    private function appendTimelineEvent(int $orderId, ?int $userId, string $eventType, string $title, ?string $description = null, array $metadata = []): void
    {
        $this->orderTimelineRepository->create([
            'order_id' => $orderId,
            'changed_by' => $userId,
            'event_type' => $eventType,
            'title' => $title,
            'description' => $description,
            'metadata' => empty($metadata) ? null : $metadata,
        ]);
    }

    private function buildItemKey(int $productId, int $quantity, float $price): string
    {
        return implode(':', [$productId, $quantity, number_format($price, 2, '.', '')]);
    }
}
