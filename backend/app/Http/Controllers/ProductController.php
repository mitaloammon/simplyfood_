<?php

namespace App\Http\Controllers;

use App\Application\Services\ProductService;
use App\Domains\Product\Product;
use App\Http\Requests\StoreProductRequest;
use App\Http\Resources\ProductResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Redirector;
use Illuminate\Support\Facades\Gate;
use Symfony\Component\HttpFoundation\Response;

class ProductController extends BaseController
{
    private ProductService $productService;

    public function __construct(ProductService $service)
    {
        parent::__construct($service);
        $this->productService = $service;
    }

    /**
     * Get active products only (Secondary scenario).
     */
    public function get(Request $request): JsonResponse
    {
        $this->authorizeForCurrentUser('viewAny', Product::class);

        $products = $this->productService->get($request->all());

        return response()->json([
            'status' => 'success',
            'data' => ProductResource::collection($products),
        ], Response::HTTP_OK);
    }

    public function getActive(): JsonResponse
    {
        $this->authorizeForCurrentUser('viewAny', Product::class);

        $products = $this->productService->get(['ativo' => true]);

        return response()->json([
            'status' => 'success',
            'data' => ProductResource::collection($products),
        ], Response::HTTP_OK);
    }

    public function post(Request $request): JsonResponse
    {
        $this->authorizeForCurrentUser('create', Product::class);

        $validated = $this->validateProductPayload($request);
        if ($request->hasFile('imagem_file')) {
            $validated['imagem_file'] = $request->file('imagem_file');
        }

        $product = $this->productService->createFromPayload($validated, $request->user());

        return response()->json([
            'status' => 'success',
            'data' => new ProductResource($product->load('category')),
        ], Response::HTTP_CREATED);
    }

    public function update(Request $request, int|string $id): JsonResponse
    {
        $product = Product::query()->findOrFail($id);
        $this->authorizeForCurrentUser('update', $product);

        $validated = $this->validateProductPayload($request);
        if ($request->hasFile('imagem_file')) {
            $validated['imagem_file'] = $request->file('imagem_file');
        }

        $updatedProduct = $this->productService->updateFromPayload($id, $validated, $request->user());

        return response()->json([
            'status' => 'success',
            'data' => new ProductResource($updatedProduct),
        ], Response::HTTP_OK);
    }

    public function deleted(int|string $id): JsonResponse
    {
        $product = Product::query()->findOrFail($id);
        $this->authorizeForCurrentUser('delete', $product);

        return parent::deleted($id);
    }

    public function show(int|string $id): JsonResponse
    {
        $product = Product::query()->findOrFail($id);
        $this->authorizeForCurrentUser('view', $product);

        $product = $this->productService->find($id);

        return response()->json([
            'status' => 'success',
            'data' => new ProductResource($product->load('category')),
        ], Response::HTTP_OK);
    }

    public function quickCreateOptions(Request $request): JsonResponse
    {
        $this->authorizeForCurrentUser('create', Product::class);

        return response()->json([
            'status' => 'success',
            'data' => $this->productService->getQuickCreateOptions($request->user()),
        ], Response::HTTP_OK);
    }

    private function validateProductPayload(Request $request): array
    {
        $formRequest = StoreProductRequest::createFrom($request);
        $formRequest->setContainer(app())->setRedirector(app(Redirector::class));
        $formRequest->setRouteResolver(fn () => $request->route());
        $formRequest->validateResolved();

        return $formRequest->validated();
    }

    private function authorizeForCurrentUser(string $ability, Product|string $subject): void
    {
        $user = request()->user();
        abort_if(!$user, Response::HTTP_FORBIDDEN, 'Forbidden: Authenticated user not found.');

        Gate::forUser($user)->authorize($ability, $subject);
    }
}
