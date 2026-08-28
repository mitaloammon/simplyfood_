<?php

namespace App\Application\Orders;

use App\Exceptions\BusinessConflictException;
use App\Models\CashRegisterShift;
use App\Models\Command;
use App\Models\DiningTable;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\User;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class OrderService
{
    private const TRANSITIONS = [
        'OPEN' => ['IN_PREPARATION', 'CANCELLED'],
        'IN_PREPARATION' => ['READY', 'CANCELLED'],
        'READY' => ['DELIVERED', 'CANCELLED'],
        'DELIVERED' => ['CLOSED'],
    ];

    public function paginate(User $user, array $filters, int $perPage): LengthAwarePaginator
    {
        return Order::query()
            ->where('establishment_id', $user->establishment_id)
            ->when($filters['status'] ?? null, fn ($query, $status) => $query->where('status', $status))
            ->when($filters['table_id'] ?? null, fn ($query, $id) => $query->where('table_id', $id))
            ->when($filters['command_id'] ?? null, fn ($query, $id) => $query->where('command_id', $id))
            ->with(['items.product', 'customer', 'table', 'command'])
            ->orderByDesc('created_at')
            ->paginate($perPage);
    }

    public function create(User $user, array $data): Order
    {
        return DB::transaction(function () use ($user, $data) {
            $shift = CashRegisterShift::query()
                ->where('establishment_id', $user->establishment_id)
                ->where('status', 'OPEN')
                ->orderByDesc('opened_at')
                ->lockForUpdate()
                ->first();

            if (! $shift) {
                throw new BusinessConflictException('Pedido exige turno de caixa aberto');
            }

            [$tableId, $commandId] = $this->resolveServiceContext($user, $data);

            $order = Order::query()->create([
                'establishment_id' => $user->establishment_id,
                'cash_register_shift_id' => $shift->id,
                'waiter_id' => $user->id,
                'customer_id' => $data['customer_id'] ?? null,
                'table_id' => $tableId,
                'command_id' => $commandId,
                'order_type' => $data['order_type'],
                'status' => 'OPEN',
                'subtotal' => 0,
                'discount' => 0,
                'total_amount' => 0,
            ]);

            foreach ($data['items'] as $itemData) {
                $this->createItem($user, $order, $itemData);
            }

            $this->recalculateTotals($order);
            $this->recordStatus($order, $user, null, 'OPEN');

            return $this->loadOrder($order->refresh());
        });
    }

    public function find(User $user, string $id): Order
    {
        $order = Order::query()
            ->where('establishment_id', $user->establishment_id)
            ->findOrFail($id);

        return $this->loadOrder($order);
    }

    public function addItem(User $user, string $orderId, array $data): Order
    {
        return DB::transaction(function () use ($user, $orderId, $data) {
            $order = $this->lockedOrder($user, $orderId);
            $this->ensureOpenForItems($order);
            $this->createItem($user, $order, $data);
            $this->recalculateTotals($order);

            return $this->loadOrder($order->refresh());
        });
    }

    public function removeItem(User $user, string $orderId, string $itemId): Order
    {
        return DB::transaction(function () use ($user, $orderId, $itemId) {
            $order = $this->lockedOrder($user, $orderId);
            $this->ensureOpenForItems($order);

            $item = OrderItem::query()
                ->where('establishment_id', $user->establishment_id)
                ->where('order_id', $order->id)
                ->lockForUpdate()
                ->findOrFail($itemId);

            $item->delete();
            $this->recalculateTotals($order);

            return $this->loadOrder($order->refresh());
        });
    }

    public function updateStatus(User $user, string $orderId, string $newStatus): Order
    {
        return DB::transaction(function () use ($user, $orderId, $newStatus) {
            $order = $this->lockedOrder($user, $orderId);
            $oldStatus = $order->status;

            if ($oldStatus === $newStatus) {
                return $this->loadOrder($order);
            }

            if (
                $newStatus === 'CANCELLED'
                && $user->role === 'WAITER'
                && ($order->waiter_id !== $user->id || $oldStatus !== 'OPEN')
            ) {
                throw new AuthorizationException(
                    'Garçom só pode cancelar o próprio pedido aberto'
                );
            }

            if (! in_array($newStatus, self::TRANSITIONS[$oldStatus] ?? [], true)) {
                throw new BusinessConflictException(
                    "Transição de {$oldStatus} para {$newStatus} não permitida"
                );
            }

            if ($newStatus === 'CLOSED') {
                $this->ensurePaid($user, $order);
                $this->ensureStock($user, $order);
            }

            $order->update(['status' => $newStatus]);
            $this->recordStatus($order, $user, $oldStatus, $newStatus);

            return $this->loadOrder($order->refresh());
        });
    }

    private function resolveServiceContext(User $user, array $data): array
    {
        if ($data['order_type'] === 'COUNTER') {
            return [null, null];
        }

        $table = DiningTable::query()
            ->where('establishment_id', $user->establishment_id)
            ->lockForUpdate()
            ->findOrFail($data['table_id']);

        if ($data['order_type'] === 'TABLE') {
            if (! in_array($table->status, ['FREE', 'OCCUPIED'], true)) {
                throw new BusinessConflictException('Pedido de mesa exige mesa FREE ou OCCUPIED');
            }

            if ($table->status === 'FREE') {
                $table->update(['status' => 'OCCUPIED']);
            }

            return [$table->id, null];
        }

        $command = Command::query()
            ->where('establishment_id', $user->establishment_id)
            ->where('status', 'OPEN')
            ->lockForUpdate()
            ->findOrFail($data['command_id']);

        if ($command->table_id !== $table->id) {
            throw new BusinessConflictException('A comanda não pertence à mesa informada');
        }

        return [$table->id, $command->id];
    }

    private function createItem(User $user, Order $order, array $data): OrderItem
    {
        $product = Product::query()
            ->where('establishment_id', $user->establishment_id)
            ->where('is_available', true)
            ->lockForUpdate()
            ->findOrFail($data['product_id']);

        $totalPrice = number_format((float) $product->price * $data['quantity'], 2, '.', '');

        return OrderItem::query()->create([
            'establishment_id' => $user->establishment_id,
            'order_id' => $order->id,
            'product_id' => $product->id,
            'quantity' => $data['quantity'],
            'unit_price' => $product->price,
            'total_price' => $totalPrice,
            'status' => 'WAITING',
            'notes' => $data['notes'] ?? null,
        ]);
    }

    private function recalculateTotals(Order $order): void
    {
        $subtotal = OrderItem::query()
            ->where('establishment_id', $order->establishment_id)
            ->where('order_id', $order->id)
            ->sum('total_price');

        $order->update(['subtotal' => $subtotal, 'total_amount' => $subtotal]);
    }

    private function ensureOpenForItems(Order $order): void
    {
        if ($order->status !== 'OPEN') {
            throw new BusinessConflictException(
                'Itens só podem ser alterados enquanto o pedido está OPEN'
            );
        }
    }

    private function ensurePaid(User $user, Order $order): void
    {
        $paid = DB::table('payments')
            ->where('establishment_id', $user->establishment_id)
            ->where('order_id', $order->id)
            ->where('status', 'CONFIRMED')
            ->whereNull('deleted_at')
            ->lockForUpdate()
            ->sum('amount');

        if ((float) $paid < (float) $order->total_amount) {
            throw new BusinessConflictException(
                'Pedido só pode ser fechado quando estiver integralmente pago'
            );
        }
    }

    private function ensureStock(User $user, Order $order): void
    {
        $insufficient = DB::table('order_items as oi')
            ->join('product_ingredients as pi', 'pi.product_id', '=', 'oi.product_id')
            ->join('inventory_items as ii', 'ii.id', '=', 'pi.inventory_item_id')
            ->where('oi.establishment_id', $user->establishment_id)
            ->where('ii.establishment_id', $user->establishment_id)
            ->where('oi.order_id', $order->id)
            ->whereNull('oi.deleted_at')
            ->whereNull('ii.deleted_at')
            ->groupBy('ii.id', 'ii.stock_quantity')
            ->havingRaw('ii.stock_quantity < SUM(oi.quantity * pi.quantity)')
            ->lockForUpdate()
            ->exists();

        if ($insufficient) {
            throw new BusinessConflictException('Estoque insuficiente para fechar o pedido');
        }
    }

    private function recordStatus(
        Order $order,
        User $user,
        ?string $from,
        string $to
    ): void {
        DB::table('order_status_history')->insert([
            'id' => (string) Str::uuid(),
            'order_id' => $order->id,
            'changed_by' => $user->id,
            'from_status' => $from,
            'to_status' => $to,
            'changed_at' => now(),
            'notes' => null,
        ]);
    }

    private function lockedOrder(User $user, string $id): Order
    {
        return Order::query()
            ->where('establishment_id', $user->establishment_id)
            ->lockForUpdate()
            ->findOrFail($id);
    }

    private function loadOrder(Order $order): Order
    {
        return $order->load(['items.product', 'customer', 'table', 'command']);
    }
}
