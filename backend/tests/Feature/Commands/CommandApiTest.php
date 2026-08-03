<?php

namespace Tests\Feature\Commands;

use App\Domains\Auth\User\User;
use App\Domains\Tables\RestaurantTable;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CommandApiTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_opens_and_closes_a_command_ticket(): void
    {
        $user = User::factory()->create(['role' => 'ADMIN']);

        $table = RestaurantTable::query()->create([
            'user_id' => $user->id,
            'number' => 10,
            'capacity' => 4,
            'status' => 'LIVRE',
        ]);

        $openResponse = $this
            ->withHeaders(['Authorization' => 'Bearer valid-' . $user->id])
            ->postJson('/api/commands', [
                'table_id' => $table->id,
                'subtotal' => 50,
                'total' => 50,
            ]);

        $openResponse->assertStatus(201)
            ->assertJsonPath('status', 'success')
            ->assertJsonPath('data.status', 'ABERTA');

        $commandId = $openResponse->json('data.id');

        $closeResponse = $this
            ->withHeaders(['Authorization' => 'Bearer valid-' . $user->id])
            ->patchJson('/api/commands/' . $commandId . '/status', [
                'status' => 'FECHADA',
            ]);

        $closeResponse->assertStatus(200)
            ->assertJsonPath('data.status', 'FECHADA');
    }
}
