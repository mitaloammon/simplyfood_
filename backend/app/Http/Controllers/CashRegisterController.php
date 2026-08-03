<?php

namespace App\Http\Controllers;

use App\Application\Services\CashRegisterService;
use App\Domains\CashRegister\CashRegister;
use App\Http\Requests\CloseCashRegisterRequest;
use App\Http\Requests\OpenCashRegisterRequest;
use App\Http\Requests\StoreCashTransactionRequest;
use App\Http\Resources\CashClosingResource;
use App\Http\Resources\CashRegisterResource;
use App\Http\Resources\CashTransactionResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use InvalidArgumentException;
use Symfony\Component\HttpFoundation\Response;

class CashRegisterController extends Controller
{
    public function __construct(private readonly CashRegisterService $service)
    {
    }

    public function open(OpenCashRegisterRequest $request): JsonResponse
    {
        Gate::forUser($request->user())->authorize('create', CashRegister::class);

        try {
            $cashRegister = $this->service->open((int) $request->user()->id, (float) $request->validated()['opening_balance']);

            return response()->json([
                'status' => 'success',
                'data' => new CashRegisterResource($cashRegister),
            ], Response::HTTP_CREATED);
        } catch (InvalidArgumentException $exception) {
            return response()->json([
                'status' => 'error',
                'message' => $exception->getMessage(),
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        }
    }

    public function current(Request $request): JsonResponse
    {
        Gate::forUser($request->user())->authorize('viewAny', CashRegister::class);

        $cashRegister = $this->service->current((int) $request->user()->id);

        return response()->json([
            'status' => 'success',
            'data' => $cashRegister ? new CashRegisterResource($cashRegister) : null,
        ], Response::HTTP_OK);
    }

    public function history(Request $request): JsonResponse
    {
        Gate::forUser($request->user())->authorize('viewAny', CashRegister::class);

        return response()->json([
            'status' => 'success',
            'data' => CashRegisterResource::collection($this->service->history((int) $request->user()->id)),
        ], Response::HTTP_OK);
    }

    public function transaction(StoreCashTransactionRequest $request): JsonResponse
    {
        Gate::forUser($request->user())->authorize('update', CashRegister::class);

        try {
            $transaction = $this->service->transaction(
                (int) $request->user()->id,
                (string) $request->validated()['type'],
                (float) $request->validated()['amount'],
                $request->validated()['description'] ?? null,
            );

            return response()->json([
                'status' => 'success',
                'data' => new CashTransactionResource($transaction),
            ], Response::HTTP_CREATED);
        } catch (InvalidArgumentException $exception) {
            return response()->json([
                'status' => 'error',
                'message' => $exception->getMessage(),
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        }
    }

    public function close(CloseCashRegisterRequest $request): JsonResponse
    {
        Gate::forUser($request->user())->authorize('update', CashRegister::class);

        try {
            $closing = $this->service->close(
                (int) $request->user()->id,
                (float) $request->validated()['declared_amount'],
                (bool) ($request->validated()['blind_closing'] ?? false),
                $request->validated()['notes'] ?? null,
            );

            return response()->json([
                'status' => 'success',
                'data' => new CashClosingResource($closing),
            ], Response::HTTP_OK);
        } catch (InvalidArgumentException $exception) {
            return response()->json([
                'status' => 'error',
                'message' => $exception->getMessage(),
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        }
    }
}
