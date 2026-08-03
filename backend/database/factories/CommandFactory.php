<?php

namespace Database\Factories;

use App\Domains\Auth\User\User;
use App\Domains\Commands\CommandTicket;
use App\Domains\Tables\RestaurantTable;
use Illuminate\Database\Eloquent\Factories\Factory;

class CommandFactory extends Factory
{
    protected $model = CommandTicket::class;

    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'table_id' => RestaurantTable::factory(),
            'customer_id' => null,
            'status' => 'ABERTA',
            'subtotal' => 0,
            'total' => 0,
            'notes' => null,
            'opened_at' => now(),
            'closed_at' => null,
        ];
    }
}
