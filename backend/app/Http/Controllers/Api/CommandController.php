<?php

namespace App\Http\Controllers\Api;

use App\Application\Service\CommandService;
use App\Http\Controllers\Controller;
use App\Http\Requests\Command\ListCommandsRequest;
use App\Http\Requests\Command\OpenCommandRequest;
use App\Http\Requests\Command\UpdateCommandStatusRequest;
use App\Http\Resources\CommandResource;
use Illuminate\Http\JsonResponse;

class CommandController extends Controller
{
    public function __construct(private readonly CommandService $commands)
    {
    }

    public function index(ListCommandsRequest $request): JsonResponse
    {
        $commands = $this->commands->paginate(
            $request->user(),
            $request->integer('per_page', 20)
        );

        $commands->through(
            fn ($command) => CommandResource::make($command)->resolve($request)
        );

        return response()->json([
            'status' => 'success',
            'data' => $commands,
            'message' => 'Comandas listadas com sucesso',
        ]);
    }

    public function store(OpenCommandRequest $request): JsonResponse
    {
        $command = $this->commands->open($request->user(), $request->validated());

        return response()->json([
            'status' => 'success',
            'data' => CommandResource::make($command)->resolve($request),
            'message' => 'Comanda aberta com sucesso',
        ], 201);
    }

    public function updateStatus(
        UpdateCommandStatusRequest $request,
        string $command
    ): JsonResponse {
        $command = $this->commands->updateStatus(
            $request->user(),
            $command,
            $request->validated('status')
        );

        return response()->json([
            'status' => 'success',
            'data' => CommandResource::make($command)->resolve($request),
            'message' => 'Status da comanda atualizado com sucesso',
        ]);
    }
}
