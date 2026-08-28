<?php

namespace App\Http\Controllers\Api;

use App\Application\Service\TableService;
use App\Http\Controllers\Controller;
use App\Http\Requests\Table\ListTablesRequest;
use App\Http\Requests\Table\StoreTableRequest;
use App\Http\Requests\Table\UpdateTableRequest;
use App\Http\Requests\Table\UpdateTableStatusRequest;
use App\Http\Resources\TableResource;
use Illuminate\Http\JsonResponse;

class TableController extends Controller
{
    public function __construct(private readonly TableService $tables)
    {
    }

    public function index(ListTablesRequest $request): JsonResponse
    {
        $tables = $this->tables->paginate(
            $request->user(),
            $request->integer('per_page', 20)
        );

        $tables->through(
            fn ($table) => TableResource::make($table)->resolve($request)
        );

        return response()->json([
            'status' => 'success',
            'data' => $tables,
            'message' => 'Mesas listadas com sucesso',
        ]);
    }

    public function store(StoreTableRequest $request): JsonResponse
    {
        $table = $this->tables->create($request->user(), $request->validated());

        return response()->json([
            'status' => 'success',
            'data' => TableResource::make($table)->resolve($request),
            'message' => 'Mesa criada com sucesso',
        ], 201);
    }

    public function update(UpdateTableRequest $request, string $table): JsonResponse
    {
        $table = $this->tables->update(
            $request->user(),
            $table,
            $request->validated()
        );

        return response()->json([
            'status' => 'success',
            'data' => TableResource::make($table)->resolve($request),
            'message' => 'Mesa atualizada com sucesso',
        ]);
    }

    public function updateStatus(
        UpdateTableStatusRequest $request,
        string $table
    ): JsonResponse {
        $table = $this->tables->updateStatus(
            $request->user(),
            $table,
            $request->validated('status')
        );

        return response()->json([
            'status' => 'success',
            'data' => TableResource::make($table)->resolve($request),
            'message' => 'Status da mesa atualizado com sucesso',
        ]);
    }
}
