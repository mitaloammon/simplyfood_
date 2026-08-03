<?php

namespace Database\Factories;

use App\Domains\Auth\User\User;
use App\Domains\Recipe\Ingredient;
use Illuminate\Database\Eloquent\Factories\Factory;

class IngredientFactory extends Factory
{
    protected $model = Ingredient::class;

    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'name' => $this->faker->unique()->word(),
            'unit' => 'G',
            'current_stock' => 1000,
            'min_stock' => 100,
            'active' => true,
        ];
    }
}
