<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Application\Services\BaseService;
use Illuminate\Http\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

abstract class BaseController extends Controller
{
    /**
     * The service instance associated with this controller.
     */
    protected BaseService $service;

    public function __construct(BaseService $service)
    {
        $this->service = $service;
    }

    /**
     * Get a list of resources.
     */
    public function get(Request $request): JsonResponse
    {
        $filters = $request->all();
        $resources = $this->service->get($filters);
        
        return response()->json([
            'status' => 'success',
            'data' => $resources,
        ], Response::HTTP_OK);
    }

    /**
     * Store a new resource.
     */
    public function post(Request $request): JsonResponse
    {
        $validated = $request->all(); // Custom request validation is injected into children if needed
        $resource = $this->service->post($validated);

        return response()->json([
            'status' => 'success',
            'data' => $resource,
        ], Response::HTTP_CREATED);
    }

    /**
     * Update an existing resource.
     */
    public function update(Request $request, int|string $id): JsonResponse
    {
        $validated = $request->all();
        $resource = $this->service->update($id, $validated);

        return response()->json([
            'status' => 'success',
            'data' => $resource,
        ], Response::HTTP_OK);
    }

    /**
     * Delete a resource.
     */
    public function deleted(int|string $id): JsonResponse
    {
        $success = $this->service->deleted($id);

        return response()->json([
            'status' => 'success',
            'success' => $success,
            'message' => $success ? 'Resource deleted successfully.' : 'Could not delete resource.',
        ], Response::HTTP_OK);
    }
}
