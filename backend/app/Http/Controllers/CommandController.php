<?php

namespace App\Http\Controllers;

use App\Application\Services\CommandService;
use App\Domains\Commands\CommandTicket;
use App\Http\Requests\StoreCommandRequest;
use App\Http\Requests\UpdateCommandStatusRequest;
use App\Http\Resources\CommandResource;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use InvalidArgumentException;
use Symfony\Component\HttpFoundation\Response;

class CommandController extends Controller
{
    public function __construct(private readonly CommandService $service)
    {
    }

    public function index(Request $request): JsonResponse
    {
        Gate::forUser($request->user())->authorize('viewAny', CommandTicket::class);

        return response()->json([
            'status' => 'success',
            'data' => CommandResource::collection($this->service->listByUser((int) $request->user()->id)),
        ], Response::HTTP_OK);
    }

    public function store(StoreCommandRequest $request): JsonResponse
    {
        Gate::forUser($request->user())->authorize('create', CommandTicket::class);

        try {
            $command = $this->service->open((int) $request->user()->id, $request->validated());

            return response()->json([
                'status' => 'success',
                'data' => new CommandResource($command),
            ], Response::HTTP_CREATED);
        } catch (InvalidArgumentException $exception) {
            return response()->json([
                'status' => 'error',
                'message' => $exception->getMessage(),
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        }
    }

    public function updateStatus(UpdateCommandStatusRequest $request, int|string $id): JsonResponse
    {
        Gate::forUser($request->user())->authorize('update', CommandTicket::class);

        try {
            $command = $this->service->updateStatus((int) $request->user()->id, $id, (string) $request->validated()['status']);

            return response()->json([
                'status' => 'success',
                'data' => new CommandResource($command),
            ], Response::HTTP_OK);
        } catch (ModelNotFoundException) {
            return response()->json([
                'status' => 'error',
                'message' => 'Comanda nao encontrada para o usuario autenticado.',
            ], Response::HTTP_NOT_FOUND);
        }
    }
}
