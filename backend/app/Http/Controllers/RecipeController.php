<?php

namespace App\Http\Controllers;

use App\Application\Services\RecipeService;
use App\Domains\Recipe\Ingredient;
use App\Domains\Recipe\Recipe;
use App\Http\Requests\ConsumeRecipeRequest;
use App\Http\Requests\StoreIngredientRequest;
use App\Http\Requests\StoreRecipeItemRequest;
use App\Http\Requests\StoreRecipeRequest;
use App\Http\Resources\IngredientResource;
use App\Http\Resources\RecipeResource;
use App\Http\Resources\StockMovementResource;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use InvalidArgumentException;
use Symfony\Component\HttpFoundation\Response;

class RecipeController extends Controller
{
    public function __construct(private readonly RecipeService $service)
    {
    }

    public function listIngredients(Request $request): JsonResponse
    {
        Gate::forUser($request->user())->authorize('viewAny', Ingredient::class);

        return response()->json([
            'status' => 'success',
            'data' => IngredientResource::collection($this->service->listIngredients((int) $request->user()->id)),
        ], Response::HTTP_OK);
    }

    public function storeIngredient(StoreIngredientRequest $request): JsonResponse
    {
        Gate::forUser($request->user())->authorize('create', Ingredient::class);

        $ingredient = $this->service->createIngredient((int) $request->user()->id, $request->validated());

        return response()->json([
            'status' => 'success',
            'data' => new IngredientResource($ingredient),
        ], Response::HTTP_CREATED);
    }

    public function listRecipes(Request $request): JsonResponse
    {
        Gate::forUser($request->user())->authorize('viewAny', Recipe::class);

        return response()->json([
            'status' => 'success',
            'data' => RecipeResource::collection($this->service->listRecipes((int) $request->user()->id)),
        ], Response::HTTP_OK);
    }

    public function storeRecipe(StoreRecipeRequest $request): JsonResponse
    {
        Gate::forUser($request->user())->authorize('create', Recipe::class);

        $recipe = $this->service->createRecipe((int) $request->user()->id, $request->validated());

        return response()->json([
            'status' => 'success',
            'data' => new RecipeResource($recipe),
        ], Response::HTTP_CREATED);
    }

    public function addRecipeItem(StoreRecipeItemRequest $request, int|string $recipeId): JsonResponse
    {
        Gate::forUser($request->user())->authorize('update', Recipe::class);

        try {
            $item = $this->service->addRecipeItem((int) $request->user()->id, $recipeId, $request->validated());

            return response()->json([
                'status' => 'success',
                'data' => [
                    'id' => $item->id,
                    'recipe_id' => $item->recipe_id,
                    'ingredient_id' => $item->ingredient_id,
                    'quantity' => (float) $item->quantity,
                ],
            ], Response::HTTP_CREATED);
        } catch (ModelNotFoundException) {
            return response()->json([
                'status' => 'error',
                'message' => 'Receita ou ingrediente nao encontrado para o usuario autenticado.',
            ], Response::HTTP_NOT_FOUND);
        }
    }

    public function consume(ConsumeRecipeRequest $request, int|string $recipeId): JsonResponse
    {
        Gate::forUser($request->user())->authorize('update', Recipe::class);

        try {
            $result = $this->service->consumeRecipe(
                (int) $request->user()->id,
                $recipeId,
                (float) ($request->validated()['multiplier'] ?? 1),
            );

            return response()->json([
                'status' => 'success',
                'data' => [
                    'recipe' => new RecipeResource($result['recipe']),
                    'movements' => StockMovementResource::collection(collect($result['movements'])),
                ],
            ], Response::HTTP_OK);
        } catch (ModelNotFoundException) {
            return response()->json([
                'status' => 'error',
                'message' => 'Receita nao encontrada para o usuario autenticado.',
            ], Response::HTTP_NOT_FOUND);
        } catch (InvalidArgumentException $exception) {
            return response()->json([
                'status' => 'error',
                'message' => $exception->getMessage(),
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        }
    }
}
