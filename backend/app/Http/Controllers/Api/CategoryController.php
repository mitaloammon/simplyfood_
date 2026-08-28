<?php

namespace App\Http\Controllers\Api;

use App\Application\Catalog\CategoryService;
use App\Http\Controllers\Controller;
use App\Http\Requests\Category\StoreCategoryRequest;
use App\Http\Requests\Category\UpdateCategoryRequest;
use App\Http\Resources\CategoryResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CategoryController extends Controller
{
    public function __construct(private readonly CategoryService $categories)
    {
    }

    public function index(Request $request): JsonResponse
    {
        $categories = $this->categories->paginate(
            $request->user(),
            min(max($request->integer('per_page', 20), 1), 100)
        );

        $categories->through(
            fn ($category) => CategoryResource::make($category)->resolve($request)
        );

        return response()->json([
            'status' => 'success',
            'data' => $categories,
            'message' => 'Categorias listadas com sucesso',
        ]);
    }

    public function store(StoreCategoryRequest $request): JsonResponse
    {
        $category = $this->categories->create($request->user(), $request->validated());

        return response()->json([
            'status' => 'success',
            'data' => CategoryResource::make($category)->resolve($request),
            'message' => 'Categoria criada com sucesso',
        ], 201);
    }

    public function update(UpdateCategoryRequest $request, string $category): JsonResponse
    {
        $category = $this->categories->update(
            $request->user(),
            $category,
            $request->validated()
        );

        return response()->json([
            'status' => 'success',
            'data' => CategoryResource::make($category)->resolve($request),
            'message' => 'Categoria atualizada com sucesso',
        ]);
    }

    public function destroy(Request $request, string $category): JsonResponse
    {
        $this->categories->delete($request->user(), $category);

        return response()->json([
            'status' => 'success',
            'data' => null,
            'message' => 'Categoria removida com sucesso',
        ]);
    }
}
