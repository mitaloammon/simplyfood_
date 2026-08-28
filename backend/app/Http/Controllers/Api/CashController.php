<?php

namespace App\Http\Controllers\Api;

use App\Application\Cash\CashService;
use App\Http\Controllers\Controller;
use App\Http\Requests\Cash\CashHistoryRequest;
use App\Http\Requests\Cash\CashMovementRequest;
use App\Http\Requests\Cash\CloseCashRequest;
use App\Http\Requests\Cash\OpenCashRequest;
use App\Http\Resources\CashMovementResource;
use App\Http\Resources\CashRegisterShiftResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CashController extends Controller
{
    public function __construct(private readonly CashService $cash)
    {
    }

    public function open(OpenCashRequest $request): JsonResponse
    {
        $shift = $this->cash->open($request->user(), $request->validated());

        return response()->json([
            'status' => 'success',
            'data' => CashRegisterShiftResource::make($shift)->resolve($request),
            'message' => 'Caixa aberto com sucesso',
        ], 201);
    }

    public function current(Request $request): JsonResponse
    {
        $shift = $this->cash->current($request->user());

        return response()->json([
            'status' => 'success',
            'data' => $shift
                ? CashRegisterShiftResource::make($shift)->resolve($request)
                : null,
            'message' => $shift ? 'Caixa atual encontrado' : 'Nenhum caixa aberto',
        ]);
    }

    public function movement(CashMovementRequest $request): JsonResponse
    {
        $movement = $this->cash->addMovement($request->user(), $request->validated());

        return response()->json([
            'status' => 'success',
            'data' => CashMovementResource::make($movement)->resolve($request),
            'message' => 'Movimento registrado com sucesso',
        ], 201);
    }

    public function history(CashHistoryRequest $request): JsonResponse
    {
        $shifts = $this->cash->history(
            $request->user(),
            $request->integer('per_page', 20)
        );

        $shifts->through(
            fn ($shift) => CashRegisterShiftResource::make($shift)->resolve($request)
        );

        return response()->json([
            'status' => 'success',
            'data' => $shifts,
            'message' => 'Histórico de caixa listado com sucesso',
        ]);
    }

    public function close(CloseCashRequest $request): JsonResponse
    {
        $shift = $this->cash->close($request->user(), $request->validated());

        return response()->json([
            'status' => 'success',
            'data' => CashRegisterShiftResource::make($shift)->resolve($request),
            'message' => 'Caixa fechado com sucesso',
        ]);
    }
}
