<?php

namespace App\Http\Controllers;

use App\Application\Services\OrderService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use InvalidArgumentException;

class OrderController extends BaseController
{
    public function __construct(OrderService $service)
    {
        parent::__construct($service);
    }

    public function show(int|string $id): JsonResponse
    {
        $order = $this->service->find($id);

        return response()->json([
            'status' => 'success',
            'data' => $order,
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
            /** @var OrderService $orderService */
            $orderService = $this->service;
            $order = $orderService->updateStatus($id, $status);

            return response()->json([
                'status' => 'success',
                'data' => $order,
                'message' => "Order status successfully updated to {$order->status}."
            ], Response::HTTP_OK);
        } catch (InvalidArgumentException $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage()
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        }
    }
}
