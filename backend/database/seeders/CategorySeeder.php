<?php

namespace Database\Seeders;

use App\Domains\Product\Category;
use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            [
                'name' => 'Lanches',
                'description' => 'Hamburgueres, sanduiches e porcoes.',
                'active' => true,
            ],
            [
                'name' => 'Pizzas',
                'description' => 'Pizzas salgadas e doces.',
                'active' => true,
            ],
            [
                'name' => 'Bebidas',
                'description' => 'Refrigerantes, sucos e agua.',
                'active' => true,
            ],
            [
                'name' => 'Sobremesas',
                'description' => 'Doces e sobremesas.',
                'active' => true,
            ],
            [
                'name' => 'Combos',
                'description' => 'Combos promocionais para operacao rapida.',
                'active' => true,
            ],
        ];

        foreach ($categories as $category) {
            Category::query()->updateOrCreate(
                ['name' => $category['name']],
                [
                    'description' => $category['description'],
                    'active' => $category['active'],
                ]
            );
        }
    }
}
