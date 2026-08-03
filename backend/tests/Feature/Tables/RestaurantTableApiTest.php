<?php

namespace Tests\Feature\Tables;

use App\Domains\Auth\User\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RestaurantTableApiTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_creates_and_updates_table_status(): void
    {
        $user = User::factory()->create(['role' => 'ADMIN']);

        $createResponse = $this
            ->withHeaders(['Authorization' => 'Bearer valid-' . $user->id])
            ->postJson('/api/tables', [
                'number' => 5,
                'capacity' => 4,
                'location' => 'Salao',
                'status' => 'LIVRE',
            ]);

        $createResponse->assertStatus(201)
            ->assertJsonPath('status', 'success')
            ->assertJsonPath('data.number', 5);

        $tableId = $createResponse->json('data.id');

        $statusResponse = $this
            ->withHeaders(['Authorization' => 'Bearer valid-' . $user->id])
            ->patchJson('/api/tables/' . $tableId . '/status', [
                'status' => 'RESERVADA',
            ]);

        $statusResponse->assertStatus(200)
            ->assertJsonPath('data.status', 'RESERVADA');
    }
}
