<?php

use App\Models\Category;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

uses(RefreshDatabase::class);

function catalogUser(string $establishmentId): User
{
    return User::query()->create([
        'id' => (string) Str::uuid(),
        'establishment_id' => $establishmentId,
        'name' => 'Administrador',
        'email' => Str::uuid().'@simplyfood.test',
        'password' => Hash::make('password'),
        'role' => 'ADMIN',
        'status' => 'ACTIVE',
    ]);
}

function catalogCategory(string $establishmentId): Category
{
    return Category::query()->create([
        'establishment_id' => $establishmentId,
        'name' => 'Lanches',
        'sort_order' => 0,
        'active' => true,
    ]);
}

function catalogProduct(string $establishmentId, string $categoryId): Product
{
    return Product::query()->create([
        'establishment_id' => $establishmentId,
        'category_id' => $categoryId,
        'name' => 'X-Burger',
        'price' => 20,
        'cost_price' => 10,
        'is_available' => true,
        'preparation_time_minutes' => 10,
    ]);
}

it('retorna conflito e mantém a categoria quando há produto ativo vinculado na mesma loja', function () {
    $establishmentId = (string) Str::uuid();
    $user = catalogUser($establishmentId);
    $category = catalogCategory($establishmentId);
    $product = catalogProduct($establishmentId, $category->id);

    $this->actingAs($user)
        ->deleteJson("/api/categories/{$category->id}")
        ->assertConflict()
        ->assertExactJson([
            'status' => 'error',
            'data' => null,
            'message' => 'Não é possível excluir a categoria enquanto houver produtos vinculados',
        ]);

    $this->assertNotSoftDeleted($category);
    $this->assertNotSoftDeleted($product);
    expect($product->refresh()->category_id)->toBe($category->id);
});

it('permite excluir a categoria quando o único produto vinculado já foi excluído', function () {
    $establishmentId = (string) Str::uuid();
    $user = catalogUser($establishmentId);
    $category = catalogCategory($establishmentId);
    $product = catalogProduct($establishmentId, $category->id);
    $product->delete();

    $this->actingAs($user)
        ->deleteJson("/api/categories/{$category->id}")
        ->assertOk()
        ->assertExactJson([
            'status' => 'success',
            'data' => null,
            'message' => 'Categoria removida com sucesso',
        ]);

    $this->assertSoftDeleted($category);
});

it('continua permitindo excluir produto que possui categoria', function () {
    $establishmentId = (string) Str::uuid();
    $user = catalogUser($establishmentId);
    $category = catalogCategory($establishmentId);
    $product = catalogProduct($establishmentId, $category->id);

    $this->actingAs($user)
        ->deleteJson("/api/products/{$product->id}")
        ->assertOk()
        ->assertExactJson([
            'status' => 'success',
            'data' => null,
            'message' => 'Produto removido com sucesso',
        ]);

    $this->assertSoftDeleted($product);
    $this->assertNotSoftDeleted($category);
});
