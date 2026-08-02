<?php

namespace Tests\Feature\Product;

use App\Domains\Auth\User\User;
use App\Domains\Product\Category;
use App\Domains\Product\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProductApiTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_creates_product_with_corporate_quick_create_fields(): void
    {
        $user = User::factory()->create(['role' => 'ADMIN']);

        $category = Category::query()->create([
            'name' => 'Lanches',
            'description' => 'Categoria de lanche',
            'active' => true,
        ]);

        $payload = [
            'nome' => 'X-Salada',
            'category_id' => $category->id,
            'preco_venda' => 28.9,
            'unidade' => 'UN',
            'descricao' => 'Hamburguer com salada',
            'codigo_barras' => '1234567890',
            'custo' => 14.5,
            'ativo' => true,
            'controla_estoque' => true,
            'produzido_cozinha' => true,
            'delivery' => true,
            'balcao' => true,
            'mesa' => true,
            'retirada' => true,
        ];

        $response = $this
            ->withHeaders(['Authorization' => 'Bearer valid-' . $user->id])
            ->postJson('/api/products', $payload);

        $response->assertStatus(201)
            ->assertJsonPath('status', 'success')
            ->assertJsonPath('data.nome', 'X-Salada')
            ->assertJsonPath('data.preco_venda', 28.9)
            ->assertJsonPath('data.unidade', 'UN')
            ->assertJsonPath('data.created_by', $user->id);

        $this->assertDatabaseHas('products', [
            'nome' => 'X-Salada',
            'category_id' => $category->id,
            'preco_venda' => 28.90,
            'unidade' => 'UN',
            'created_by' => $user->id,
            'updated_by' => $user->id,
        ]);
    }

    /** @test */
    public function it_returns_validation_errors_for_required_quick_create_fields(): void
    {
        $user = User::factory()->create(['role' => 'ADMIN']);

        $response = $this
            ->withHeaders(['Authorization' => 'Bearer valid-' . $user->id])
            ->postJson('/api/products', []);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['nome', 'category_id', 'preco_venda', 'preco', 'unidade']);
    }

    /** @test */
    public function it_exposes_quick_create_options_for_frontend_modal(): void
    {
        $user = User::factory()->create(['role' => 'ADMIN']);

        Category::query()->create([
            'name' => 'Bebidas',
            'description' => null,
            'active' => true,
        ]);

        $response = $this
            ->withHeaders(['Authorization' => 'Bearer valid-' . $user->id])
            ->getJson('/api/products/quick-create/options');

        $response->assertStatus(200)
            ->assertJsonPath('status', 'success')
            ->assertJsonStructure([
                'data' => [
                    'categories',
                    'units',
                    'defaults',
                    'permissions',
                    'validation_messages',
                ],
            ]);
    }

    /** @test */
    public function it_enforces_unique_barcode_when_informed(): void
    {
        $user = User::factory()->create(['role' => 'ADMIN']);

        $category = Category::query()->create([
            'name' => 'Sobremesas',
            'description' => null,
            'active' => true,
        ]);

        Product::query()->create([
            'category_id' => $category->id,
            'nome' => 'Brownie',
            'descricao' => null,
            'preco' => 12,
            'preco_venda' => 12,
            'unidade' => 'UN',
            'codigo_barras' => '9988776655',
            'ativo' => true,
        ]);

        $payload = [
            'nome' => 'Brownie Especial',
            'category_id' => $category->id,
            'preco_venda' => 13,
            'unidade' => 'UN',
            'codigo_barras' => '9988776655',
        ];

        $response = $this
            ->withHeaders(['Authorization' => 'Bearer valid-' . $user->id])
            ->postJson('/api/products', $payload);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['codigo_barras']);
    }
}
