<?php

namespace App\Application\Services;

use App\Domains\Recipe\Ingredient;
use App\Domains\Recipe\Recipe;
use App\Domains\Recipe\RecipeItem;
use App\Domains\Stock\StockMovement;
use App\Infrastructure\Repositories\IngredientRepository;
use App\Infrastructure\Repositories\RecipeItemRepository;
use App\Infrastructure\Repositories\RecipeRepository;
use App\Infrastructure\Repositories\StockMovementRepository;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class RecipeService
{
    public function __construct(
        private readonly IngredientRepository $ingredientRepository,
        private readonly RecipeRepository $recipeRepository,
        private readonly RecipeItemRepository $recipeItemRepository,
        private readonly StockMovementRepository $stockMovementRepository,
    ) {
    }

    public function createIngredient(int $userId, array $data): Ingredient
    {
        return $this->ingredientRepository->create([
            'user_id' => $userId,
            'name' => $data['name'],
            'unit' => strtoupper((string) $data['unit']),
            'current_stock' => (float) ($data['current_stock'] ?? 0),
            'min_stock' => (float) ($data['min_stock'] ?? 0),
            'active' => (bool) ($data['active'] ?? true),
        ]);
    }

    public function listIngredients(int $userId): Collection
    {
        return $this->ingredientRepository->getByUser($userId);
    }

    public function createRecipe(int $userId, array $data): Recipe
    {
        return $this->recipeRepository->create([
            'user_id' => $userId,
            'product_id' => (int) $data['product_id'],
            'name' => $data['name'],
            'yield_quantity' => (float) ($data['yield_quantity'] ?? 1),
            'active' => (bool) ($data['active'] ?? true),
        ])->load(['product', 'items.ingredient']);
    }

    public function listRecipes(int $userId): Collection
    {
        return $this->recipeRepository->getByUser($userId);
    }

    public function addRecipeItem(int $userId, int|string $recipeId, array $data): RecipeItem
    {
        $recipe = $this->recipeRepository->findByUser($recipeId, $userId);
        if (!$recipe) {
            throw (new ModelNotFoundException())->setModel(Recipe::class, [(string) $recipeId]);
        }

        $ingredient = $this->ingredientRepository->findByUser((int) $data['ingredient_id'], $userId);
        if (!$ingredient) {
            throw (new ModelNotFoundException())->setModel(Ingredient::class, [(string) $data['ingredient_id']]);
        }

        return $this->recipeItemRepository->create([
            'recipe_id' => $recipe->id,
            'ingredient_id' => $ingredient->id,
            'quantity' => (float) $data['quantity'],
        ]);
    }

    public function consumeRecipe(int $userId, int|string $recipeId, float $multiplier = 1): array
    {
        $recipe = $this->recipeRepository->findByUser($recipeId, $userId);
        if (!$recipe) {
            throw (new ModelNotFoundException())->setModel(Recipe::class, [(string) $recipeId]);
        }

        if ($multiplier <= 0) {
            throw new InvalidArgumentException('Multiplicador deve ser maior que zero.');
        }

        return DB::transaction(function () use ($recipe, $multiplier, $userId) {
            $movements = [];

            foreach ($recipe->items as $item) {
                $ingredient = $item->ingredient;
                $requiredQty = (float) $item->quantity * $multiplier;
                $before = (float) $ingredient->current_stock;
                $after = $before - $requiredQty;

                if ($after < 0) {
                    throw new InvalidArgumentException('Estoque insuficiente para o ingrediente ' . $ingredient->name . '.');
                }

                $ingredient->update(['current_stock' => $after]);

                $movement = $this->stockMovementRepository->create([
                    'user_id' => $userId,
                    'ingredient_id' => $ingredient->id,
                    'movement_type' => 'CONSUMPTION',
                    'quantity' => $requiredQty,
                    'balance_before' => $before,
                    'balance_after' => $after,
                    'reference_type' => 'recipe',
                    'reference_id' => $recipe->id,
                    'notes' => 'Consumo automatico por ficha tecnica.',
                    'moved_at' => now(),
                ]);

                $movements[] = $movement;
            }

            return [
                'recipe' => $recipe->fresh(['product', 'items.ingredient']),
                'movements' => $movements,
            ];
        });
    }
}
