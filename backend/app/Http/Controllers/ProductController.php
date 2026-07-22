<?php

namespace App\Http\Controllers;

use App\Application\Services\ProductService;
use Illuminate\Http\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

class ProductController extends BaseController
{
    public function __construct(ProductService $service)
    {
        parent::__construct($service);
    }

    /**
     * Get active products only (Secondary scenario).
     */
    public function getActive(): JsonResponse
    {
        $products = $this->service->get(['ativo' => true]);

        return response()->json([
            'status' => 'success',
            'data' => $products,
        ], Response::HTTP_OK);
    }

    public function show(int|string $id): JsonResponse
    {
        $product = $this->service->find($id);

        return response()->json([
            'status' => 'success',
            'data' => $product,
        ], Response::HTTP_OK);
    }
}
