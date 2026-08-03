<?php

namespace App\Http\Controllers;

use App\Application\Services\RestaurantTableService;
use App\Domains\Tables\RestaurantTable;
use App\Http\Requests\StoreRestaurantTableRequest;
use App\Http\Requests\UpdateRestaurantTableStatusRequest;
use App\Http\Resources\RestaurantTableResource;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Symfony\Component\HttpFoundation\Response;

class RestaurantTableController extends Controller
{
    public function __construct(private readonly RestaurantTableService $service)
    {
    }

    public function index(Request $request): JsonResponse
    {
        Gate::forUser($request->user())->authorize('viewAny', RestaurantTable::class);

        return response()->json([
            'status' => 'success',
            'data' => RestaurantTableResource::collection($this->service->listByUser((int) $request->user()->id)),
        ], Response::HTTP_OK);
    }

    public function store(StoreRestaurantTableRequest $request): JsonResponse
    {
        Gate::forUser($request->user())->authorize('create', RestaurantTable::class);

        $table = $this->service->create((int) $request->user()->id, $request->validated());

        return response()->json([
            'status' => 'success',
            'data' => new RestaurantTableResource($table),
        ], Response::HTTP_CREATED);
    }

    public function updateStatus(UpdateRestaurantTableStatusRequest $request, int|string $id): JsonResponse
    {
        Gate::forUser($request->user())->authorize('update', RestaurantTable::class);

        try {
            $table = $this->service->updateStatus((int) $request->user()->id, $id, (string) $request->validated()['status']);

            return response()->json([
                'status' => 'success',
                'data' => new RestaurantTableResource($table),
            ], Response::HTTP_OK);
        } catch (ModelNotFoundException) {
            return response()->json([
                'status' => 'error',
                'message' => 'Mesa nao encontrada para o usuario autenticado.',
            ], Response::HTTP_NOT_FOUND);
        }
    }
}
