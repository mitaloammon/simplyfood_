<?php

namespace Tests\Feature\Recipe;

use App\Domains\Auth\User\User;
use App\Domains\Product\Category;
use App\Domains\Product\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RecipeApiTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_creates_recipe_and_consumes_stock_without_negative_balance(): void
    {
        $user = User::factory()->create(['role' => 'ADMIN']);

        $category = Category::query()->create([
            'name' => 'Pizzas',
            'description' => null,
            'active' => true,
        ]);

        $product = Product::query()->create([
            'category_id' => $category->id,
            'nome' => 'Pizza Margherita',
            'preco' => 30,
            'preco_venda' => 30,
            'unidade' => 'UN',
            'ativo' => true,
        ]);

        $ingredientResponse = $this
            ->withHeaders(['Authorization' => 'Bearer valid-' . $user->id])
            ->postJson('/api/ingredients', [
                'name' => 'Farinha',
                'unit' => 'G',
                'current_stock' => 1000,
                'min_stock' => 100,
            ]);

        $ingredientResponse->assertStatus(201);
        $ingredientId = $ingredientResponse->json('data.id');

        $recipeResponse = $this
            ->withHeaders(['Authorization' => 'Bearer valid-' . $user->id])
            ->postJson('/api/recipes', [
                'product_id' => $product->id,
                'name' => 'Ficha Pizza',
                'yield_quantity' => 1,
            ]);

        $recipeResponse->assertStatus(201);
        $recipeId = $recipeResponse->json('data.id');

        $this
            ->withHeaders(['Authorization' => 'Bearer valid-' . $user->id])
            ->postJson('/api/recipes/' . $recipeId . '/items', [
                'ingredient_id' => $ingredientId,
                'quantity' => 300,
            ])
            ->assertStatus(201);

        $consumeResponse = $this
            ->withHeaders(['Authorization' => 'Bearer valid-' . $user->id])
            ->postJson('/api/recipes/' . $recipeId . '/consume', [
                'multiplier' => 1,
            ]);

        $consumeResponse->assertStatus(200)
            ->assertJsonPath('status', 'success');

        $this->assertDatabaseHas('ingredients', [
            'id' => $ingredientId,
            'current_stock' => 700,
        ]);
    }
}
