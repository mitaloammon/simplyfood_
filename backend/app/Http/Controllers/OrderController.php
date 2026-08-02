<?php

namespace App\Http\Controllers;

use App\Application\Services\OrderService;
use App\Http\Resources\OrderManagementResource;
use App\Http\Resources\OrderResource;
use App\Http\Resources\OrderTimelineResource;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use InvalidArgumentException;

class OrderController extends BaseController
{
    protected OrderService $orderService;

    public function __construct(OrderService $service)
    {
        parent::__construct($service);
        $this->orderService = $service;
    }

    public function get(Request $request): JsonResponse
    {
        $orders = $this->orderService->getByUser($request->user()->id, $request->all());

        return response()->json([
            'status' => 'success',
            'data' => OrderResource::collection($orders),
        ], Response::HTTP_OK);
    }

    public function management(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'order_number' => 'nullable|integer|min:1',
            'customer' => 'nullable|string|max:120',
            'operator' => 'nullable|string|max:120',
            'status' => 'nullable|string|max:50',
            'order_type' => 'nullable|string|max:30',
            'date' => 'nullable|date',
            'value' => 'nullable|numeric|min:0',
            'value_min' => 'nullable|numeric|min:0',
            'value_max' => 'nullable|numeric|min:0',
            'per_page' => 'nullable|integer|min:5|max:100',
            'page' => 'nullable|integer|min:1',
        ]);

        $perPage = isset($validated['per_page']) ? (int) $validated['per_page'] : 15;
        $paginated = $this->orderService->getManagementPageByUser($request->user()->id, $validated, $perPage);

        return response()->json([
            'status' => 'success',
            'data' => OrderManagementResource::collection($paginated->items()),
            'meta' => [
                'current_page' => $paginated->currentPage(),
                'last_page' => $paginated->lastPage(),
                'per_page' => $paginated->perPage(),
                'total' => $paginated->total(),
            ],
        ], Response::HTTP_OK);
    }

    public function show(Request $request, int|string $id): JsonResponse
    {
        $order = $this->orderService->findByUserOrFail($id, $request->user()->id);

        return response()->json([
            'status' => 'success',
            'data' => new OrderResource($order),
        ], Response::HTTP_OK);
    }

    public function post(Request $request): JsonResponse
    {
        $payload = $request->validate([
            'customer_id' => 'nullable|integer|exists:customers,id',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|integer|exists:products,id',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.price' => 'required|numeric|min:0',
            'status' => 'nullable|string|max:50',
            'order_type' => 'nullable|string|in:MESA,DELIVERY,BALCAO',
            'discount' => 'nullable|numeric|min:0',
            'surcharge' => 'nullable|numeric|min:0',
            'notes' => 'nullable|string|max:1500',
            'total' => 'nullable|numeric|min:0',
        ]);
        $payload['user_id'] = $request->user()->id;

        $order = $this->orderService->postByUser($request->user()->id, $payload);

        return response()->json([
            'status' => 'success',
            'data' => new OrderResource($order),
        ], Response::HTTP_CREATED);
    }

    public function update(Request $request, int|string $id): JsonResponse
    {
        $payload = $request->validate([
            'customer_id' => 'sometimes|nullable|integer|exists:customers,id',
            'items' => 'sometimes|array|min:1',
            'items.*.product_id' => 'required_with:items|integer|exists:products,id',
            'items.*.quantity' => 'required_with:items|integer|min:1',
            'items.*.price' => 'required_with:items|numeric|min:0',
            'status' => 'nullable|string|max:50',
            'order_type' => 'nullable|string|in:MESA,DELIVERY,BALCAO',
            'discount' => 'nullable|numeric|min:0',
            'surcharge' => 'nullable|numeric|min:0',
            'notes' => 'nullable|string|max:1500',
            'total' => 'nullable|numeric|min:0',
        ]);

        $order = $this->orderService->updateByUser($id, $request->user()->id, $payload);

        return response()->json([
            'status' => 'success',
            'data' => new OrderResource($order),
        ], Response::HTTP_OK);
    }

    public function deleted(int|string $id): JsonResponse
    {
        $user = request()->user();
        $success = $this->orderService->deleteByUser($id, $user->id);

        return response()->json([
            'status' => 'success',
            'success' => $success,
            'message' => $success ? 'Pedido removido com sucesso.' : 'Nao foi possivel remover o pedido.',
        ], Response::HTTP_OK);
    }

    /**
     * Transition order status (Secondary scenario).
     */
    public function changeStatus(Request $request, int|string $id): JsonResponse
    {
        $status = $request->input('status');

        if (!$status) {
            return response()->json([
                'status' => 'error',
                'message' => 'The status field is required.'
            ], Response::HTTP_BAD_REQUEST);
        }

        try {
            $order = $this->orderService->updateStatusByUser($id, $request->user()->id, $status);

            return response()->json([
                'status' => 'success',
                'data' => new OrderResource($order),
                'message' => "Order status successfully updated to {$order->status}."
            ], Response::HTTP_OK);
        } catch (InvalidArgumentException $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage()
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        }
    }

    public function associateCustomer(Request $request, int|string $id): JsonResponse
    {
        $validated = $request->validate([
            'customer_id' => 'required|integer|exists:customers,id',
        ]);

        $order = $this->orderService->associateCustomerByUser($id, $request->user()->id, (int) $validated['customer_id']);

        return response()->json([
            'status' => 'success',
            'message' => 'Cliente associado ao pedido com sucesso.',
            'data' => new OrderResource($order),
        ], Response::HTTP_OK);
    }

    public function timeline(Request $request, int|string $id): JsonResponse
    {
        $timeline = $this->orderService->getTimelineByUser($id, $request->user()->id);

        return response()->json([
            'status' => 'success',
            'data' => OrderTimelineResource::collection($timeline),
        ], Response::HTTP_OK);
    }
}
