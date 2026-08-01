<?php

namespace Tests\Feature\Order;

use App\Domains\Auth\User\User;
use App\Domains\Customer\Customer;
use App\Domains\Order\Order;
use App\Domains\Product\Category;
use App\Domains\Product\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OrderApiTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_creates_an_order_for_authenticated_user(): void
    {
        $user = User::factory()->create(['role' => 'ADMIN']);

        $customer = Customer::query()->create([
            'user_id' => $user->id,
            'name' => 'Cliente Pedido',
            'phone' => '11999998888',
            'whatsapp' => '11999998888',
        ]);

        $category = Category::query()->create([
            'name' => 'Categoria Teste',
            'description' => 'Teste',
            'active' => true,
        ]);

        $product = Product::query()->create([
            'category_id' => $category->id,
            'nome' => 'Produto Teste',
            'descricao' => 'Produto para teste',
            'preco' => 12.50,
            'tempo_preparo' => 10,
            'ativo' => true,
        ]);

        $payload = [
            'customer_id' => $customer->id,
            'items' => [
                [
                    'product_id' => $product->id,
                    'quantity' => 2,
                    'price' => 12.50,
                ],
            ],
            'status' => 'WAITING_PAYMENT',
            'total' => 25.00,
        ];

        $response = $this
            ->withHeaders(['Authorization' => 'Bearer valid-' . $user->id])
            ->postJson('/api/orders', $payload);

        $response->assertStatus(201)
            ->assertJsonPath('status', 'success')
            ->assertJsonPath('data.user_id', $user->id)
            ->assertJsonPath('data.customer_id', $customer->id);

        $this->assertDatabaseHas('orders', [
            'user_id' => $user->id,
            'customer_id' => $customer->id,
        ]);
    }

    /** @test */
    public function it_lists_only_orders_owned_by_authenticated_user(): void
    {
        $owner = User::factory()->create(['role' => 'ADMIN']);
        $otherUser = User::factory()->create(['role' => 'ADMIN']);

        $ownerCustomer = Customer::query()->create([
            'user_id' => $owner->id,
            'name' => 'Cliente Owner',
            'phone' => '11999991111',
            'whatsapp' => '11999991111',
        ]);

        $otherCustomer = Customer::query()->create([
            'user_id' => $otherUser->id,
            'name' => 'Cliente Outro',
            'phone' => '11999992222',
            'whatsapp' => '11999992222',
        ]);

        Order::query()->create([
            'user_id' => $owner->id,
            'customer_id' => $ownerCustomer->id,
            'status' => 'WAITING_PAYMENT',
            'total' => 10,
        ]);

        Order::query()->create([
            'user_id' => $otherUser->id,
            'customer_id' => $otherCustomer->id,
            'status' => 'WAITING_PAYMENT',
            'total' => 20,
        ]);

        $response = $this
            ->withHeaders(['Authorization' => 'Bearer valid-' . $owner->id])
            ->getJson('/api/orders');

        $response->assertStatus(200)
            ->assertJsonPath('status', 'success');

        $orders = $response->json('data');
        $this->assertCount(1, $orders);
        $this->assertSame($owner->id, $orders[0]['user_id']);
    }
}
