<?php

namespace Database\Factories;

use App\Domains\Auth\User\User;
use App\Domains\Product\Category;
use App\Domains\Product\Product;
use App\Domains\Recipe\Recipe;
use Illuminate\Database\Eloquent\Factories\Factory;

class RecipeFactory extends Factory
{
    protected $model = Recipe::class;

    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'product_id' => function () {
                $category = Category::query()->create([
                    'name' => 'Factory Category ' . uniqid(),
                    'description' => null,
                    'active' => true,
                ]);

                return Product::query()->create([
                    'category_id' => $category->id,
                    'nome' => 'Factory Product ' . uniqid(),
                    'preco' => 10,
                    'preco_venda' => 10,
                    'unidade' => 'UN',
                    'ativo' => true,
                ])->id;
            },
            'name' => $this->faker->word(),
            'yield_quantity' => 1,
            'active' => true,
        ];
    }
}
