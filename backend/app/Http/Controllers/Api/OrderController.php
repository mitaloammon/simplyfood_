<?php

namespace App\Http\Controllers\Api;

use App\Application\Orders\OrderService;
use App\Http\Controllers\Controller;
use App\Http\Requests\Order\AddOrderItemRequest;
use App\Http\Requests\Order\ListOrdersRequest;
use App\Http\Requests\Order\StoreOrderRequest;
use App\Http\Requests\Order\UpdateOrderStatusRequest;
use App\Http\Resources\OrderResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class OrderController extends Controller
{
    public function __construct(private readonly OrderService $orders)
    {
    }

    public function index(ListOrdersRequest $request): JsonResponse
    {
        $orders = $this->orders->paginate(
            $request->user(),
            $request->safe()->only(['status', 'table_id', 'command_id']),
            $request->integer('per_page', 20)
        );

        $orders->through(
            fn ($order) => OrderResource::make($order)->resolve($request)
        );

        return response()->json([
            'status' => 'success',
            'data' => $orders,
            'message' => 'Pedidos listados com sucesso',
        ]);
    }

    public function store(StoreOrderRequest $request): JsonResponse
    {
        $order = $this->orders->create($request->user(), $request->validated());

        return response()->json([
            'status' => 'success',
            'data' => OrderResource::make($order)->resolve($request),
            'message' => 'Pedido criado com sucesso',
        ], 201);
    }

    public function show(Request $request, string $order): JsonResponse
    {
        $order = $this->orders->find($request->user(), $order);

        return response()->json([
            'status' => 'success',
            'data' => OrderResource::make($order)->resolve($request),
            'message' => 'Pedido encontrado com sucesso',
        ]);
    }

    public function addItem(AddOrderItemRequest $request, string $order): JsonResponse
    {
        $order = $this->orders->addItem(
            $request->user(),
            $order,
            $request->validated()
        );

        return response()->json([
            'status' => 'success',
            'data' => OrderResource::make($order)->resolve($request),
            'message' => 'Item adicionado com sucesso',
        ], 201);
    }

    public function updateStatus(
        UpdateOrderStatusRequest $request,
        string $order
    ): JsonResponse {
        $order = $this->orders->updateStatus(
            $request->user(),
            $order,
            $request->validated('status')
        );

        return response()->json([
            'status' => 'success',
            'data' => OrderResource::make($order)->resolve($request),
            'message' => 'Status do pedido atualizado com sucesso',
        ]);
    }

    public function removeItem(
        Request $request,
        string $order,
        string $item
    ): JsonResponse {
        $order = $this->orders->removeItem($request->user(), $order, $item);

        return response()->json([
            'status' => 'success',
            'data' => OrderResource::make($order)->resolve($request),
            'message' => 'Item removido com sucesso',
        ]);
    }
}
