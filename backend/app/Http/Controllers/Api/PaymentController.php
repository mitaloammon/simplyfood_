<?php

namespace App\Http\Controllers\Api;

use App\Application\Payments\PaymentService;
use App\Http\Controllers\Controller;
use App\Http\Requests\Payment\StorePaymentRequest;
use App\Http\Resources\PaymentResource;
use Illuminate\Http\JsonResponse;

class PaymentController extends Controller
{
    public function __construct(private readonly PaymentService $payments)
    {
    }

    public function store(
        StorePaymentRequest $request,
        string $order
    ): JsonResponse {
        $result = $this->payments->create(
            $request->user(),
            $order,
            $request->validated()
        );

        return response()->json([
            'status' => 'success',
            'data' => [
                'payment' => PaymentResource::make($result['payment'])->resolve($request),
                'paid_amount' => $result['paid_amount'],
                'remaining_amount' => $result['remaining_amount'],
                'fully_paid' => $result['fully_paid'],
            ],
            'message' => 'Pagamento registrado com sucesso',
        ], 201);
    }
}
