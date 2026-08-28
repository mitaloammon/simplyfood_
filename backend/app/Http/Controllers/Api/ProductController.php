<?php

namespace App\Http\Controllers\Api;

use App\Application\Catalog\ProductService;
use App\Http\Controllers\Controller;
use App\Http\Requests\Product\ListProductsRequest;
use App\Http\Requests\Product\StoreProductRequest;
use App\Http\Requests\Product\UpdateProductRequest;
use App\Http\Resources\ProductResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    public function __construct(private readonly ProductService $products)
    {
    }

    public function index(ListProductsRequest $request): JsonResponse
    {
        $filters = $request->safe()->only(['category_id', 'is_available', 'q']);
        $products = $this->products->paginate(
            $request->user(),
            $filters,
            $request->integer('per_page', 20)
        );

        $products->through(
            fn ($product) => ProductResource::make($product)->resolve($request)
        );

        return response()->json([
            'status' => 'success',
            'data' => $products,
            'message' => 'Produtos listados com sucesso',
        ]);
    }

    public function store(StoreProductRequest $request): JsonResponse
    {
        $product = $this->products->create($request->user(), $request->validated());

        return response()->json([
            'status' => 'success',
            'data' => ProductResource::make($product)->resolve($request),
            'message' => 'Produto criado com sucesso',
        ], 201);
    }

    public function show(Request $request, string $product): JsonResponse
    {
        $product = $this->products->find($request->user(), $product);

        return response()->json([
            'status' => 'success',
            'data' => ProductResource::make($product)->resolve($request),
            'message' => 'Produto encontrado com sucesso',
        ]);
    }

    public function update(UpdateProductRequest $request, string $product): JsonResponse
    {
        $product = $this->products->update(
            $request->user(),
            $product,
            $request->validated()
        );

        return response()->json([
            'status' => 'success',
            'data' => ProductResource::make($product)->resolve($request),
            'message' => 'Produto atualizado com sucesso',
        ]);
    }

    public function destroy(Request $request, string $product): JsonResponse
    {
        $this->products->delete($request->user(), $product);

        return response()->json([
            'status' => 'success',
            'data' => null,
            'message' => 'Produto removido com sucesso',
        ]);
    }
}
