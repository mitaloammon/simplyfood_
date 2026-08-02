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

    private function createCategoryAndProduct(): Product
    {
        $category = Category::query()->create([
            'name' => 'Categoria Teste',
            'description' => 'Teste',
            'active' => true,
        ]);

        return Product::query()->create([
            'category_id' => $category->id,
            'nome' => 'Produto Teste',
            'descricao' => 'Produto para teste',
            'preco' => 12.50,
            'tempo_preparo' => 10,
            'ativo' => true,
        ]);
    }

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

        $product = $this->createCategoryAndProduct();

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

    /** @test */
    public function it_lists_management_orders_paginated_and_scoped_to_authenticated_user(): void
    {
        $owner = User::factory()->create(['role' => 'ADMIN']);
        $otherUser = User::factory()->create(['role' => 'ADMIN']);

        $ownerCustomer = Customer::query()->create([
            'user_id' => $owner->id,
            'name' => 'Cliente Alfa',
            'phone' => '11999993333',
            'whatsapp' => '11999993333',
        ]);

        $otherCustomer = Customer::query()->create([
            'user_id' => $otherUser->id,
            'name' => 'Cliente Beta',
            'phone' => '11999994444',
            'whatsapp' => '11999994444',
        ]);

        Order::query()->create([
            'user_id' => $owner->id,
            'customer_id' => $ownerCustomer->id,
            'status' => 'WAITING_PAYMENT',
            'order_type' => 'DELIVERY',
            'total' => 45.50,
        ]);

        Order::query()->create([
            'user_id' => $otherUser->id,
            'customer_id' => $otherCustomer->id,
            'status' => 'WAITING_PAYMENT',
            'order_type' => 'MESA',
            'total' => 99.90,
        ]);

        $response = $this
            ->withHeaders(['Authorization' => 'Bearer valid-' . $owner->id])
            ->getJson('/api/orders/management?customer=Alfa&status=WAITING_PAYMENT&order_type=DELIVERY&per_page=10');

        $response->assertStatus(200)
            ->assertJsonPath('status', 'success')
            ->assertJsonPath('meta.current_page', 1)
            ->assertJsonPath('meta.per_page', 10)
            ->assertJsonPath('meta.total', 1)
            ->assertJsonPath('data.0.customer.name', 'Cliente Alfa')
            ->assertJsonPath('data.0.status', 'WAITING_PAYMENT')
            ->assertJsonPath('data.0.order_type', 'DELIVERY');
    }

    /** @test */
    public function it_associates_customer_and_appends_timeline_event(): void
    {
        $user = User::factory()->create(['role' => 'ADMIN']);

        $customer = Customer::query()->create([
            'user_id' => $user->id,
            'name' => 'Cliente Associado',
            'phone' => '11999995555',
            'whatsapp' => '11999995555',
        ]);

        $order = Order::query()->create([
            'user_id' => $user->id,
            'customer_id' => null,
            'status' => 'WAITING_PAYMENT',
            'order_type' => 'BALCAO',
            'total' => 18.00,
        ]);

        $response = $this
            ->withHeaders(['Authorization' => 'Bearer valid-' . $user->id])
            ->patchJson('/api/orders/' . $order->id . '/associate-customer', [
                'customer_id' => $customer->id,
            ]);

        $response->assertStatus(200)
            ->assertJsonPath('status', 'success')
            ->assertJsonPath('data.customer_id', $customer->id);

        $this->assertDatabaseHas('order_timelines', [
            'order_id' => $order->id,
            'event_type' => 'CUSTOMER_ASSOCIATED',
            'changed_by' => $user->id,
        ]);
    }

    /** @test */
    public function it_changes_status_and_registers_operational_timeline_events(): void
    {
        $user = User::factory()->create(['role' => 'ADMIN']);

        $customer = Customer::query()->create([
            'user_id' => $user->id,
            'name' => 'Cliente Status',
            'phone' => '11999996666',
            'whatsapp' => '11999996666',
        ]);

        $order = Order::query()->create([
            'user_id' => $user->id,
            'customer_id' => $customer->id,
            'status' => 'PAID',
            'order_type' => 'MESA',
            'total' => 22.00,
        ]);

        $response = $this
            ->withHeaders(['Authorization' => 'Bearer valid-' . $user->id])
            ->patchJson('/api/orders/' . $order->id . '/status', [
                'status' => 'PREPARING',
            ]);

        $response->assertStatus(200)
            ->assertJsonPath('status', 'success')
            ->assertJsonPath('data.status', 'PREPARING');

        $this->assertDatabaseHas('order_timelines', [
            'order_id' => $order->id,
            'event_type' => 'STATUS_CHANGED',
            'changed_by' => $user->id,
        ]);

        $this->assertDatabaseHas('order_timelines', [
            'order_id' => $order->id,
            'event_type' => 'SENT_TO_PRODUCTION',
            'changed_by' => $user->id,
        ]);
    }

    /** @test */
    public function it_prevents_timeline_access_for_orders_from_another_user_scope(): void
    {
        $owner = User::factory()->create(['role' => 'ADMIN']);
        $otherUser = User::factory()->create(['role' => 'ADMIN']);

        $ownerCustomer = Customer::query()->create([
            'user_id' => $owner->id,
            'name' => 'Cliente Dono',
            'phone' => '11999997777',
            'whatsapp' => '11999997777',
        ]);

        $order = Order::query()->create([
            'user_id' => $owner->id,
            'customer_id' => $ownerCustomer->id,
            'status' => 'WAITING_PAYMENT',
            'order_type' => 'BALCAO',
            'total' => 30.00,
        ]);

        $response = $this
            ->withHeaders(['Authorization' => 'Bearer valid-' . $otherUser->id])
            ->getJson('/api/orders/' . $order->id . '/timeline');

        $response->assertStatus(404);
    }
}
