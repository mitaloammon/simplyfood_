<?php

namespace Database\Factories;

use App\Domains\Auth\User\User;
use App\Domains\Tables\RestaurantTable;
use Illuminate\Database\Eloquent\Factories\Factory;

class RestaurantTableFactory extends Factory
{
    protected $model = RestaurantTable::class;

    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'number' => $this->faker->unique()->numberBetween(1, 200),
            'capacity' => $this->faker->numberBetween(2, 8),
            'location' => 'Salao',
            'status' => 'LIVRE',
            'description' => null,
        ];
    }
}
