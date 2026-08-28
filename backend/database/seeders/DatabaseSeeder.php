<?php

namespace Database\Seeders;

use App\Models\Establishment;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $estId = (string) Str::uuid();
        Establishment::query()->create([
            'id' => $estId,
            'name' => 'SimplyFood Demo',
            'status' => 'ACTIVE',
        ]);

        $roles = ['ADMIN', 'MANAGER', 'CASHIER', 'WAITER', 'KITCHEN'];
        foreach ($roles as $role) {
            User::query()->create([
                'id' => (string) Str::uuid(),
                'establishment_id' => $estId,
                'name' => $role,
                'email' => strtolower($role).'@simplyfood.test',
                'password' => Hash::make('password'),
                'role' => $role,
                'status' => 'ACTIVE',
            ]);
        }

        $registerId = (string) Str::uuid();
        DB::table('cash_registers')->insert([
            'id' => $registerId,
            'establishment_id' => $estId,
            'name' => 'Caixa 1',
            'is_active' => 1,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('tables')->insert([
            [
                'id' => (string) Str::uuid(),
                'establishment_id' => $estId,
                'number' => 1,
                'capacity' => 4,
                'status' => 'FREE',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => (string) Str::uuid(),
                'establishment_id' => $estId,
                'number' => 2,
                'capacity' => 4,
                'status' => 'FREE',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);

        $foodCategoryId = (string) Str::uuid();
        DB::table('categories')->insert([
            'id' => $foodCategoryId,
            'establishment_id' => $estId,
            'name' => 'Cardápio',
            'sort_order' => 1,
            'active' => 1,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('products')->insert([
            [
                'id' => (string) Str::uuid(),
                'establishment_id' => $estId,
                'category_id' => $foodCategoryId,
                'name' => 'X-Burger',
                'price' => 18.90,
                'cost_price' => 8.00,
                'sku' => 'LANCHE-001',
                'is_available' => 1,
                'preparation_time_minutes' => 15,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => (string) Str::uuid(),
                'establishment_id' => $estId,
                'category_id' => $foodCategoryId,
                'name' => 'Batata frita',
                'price' => 12.00,
                'cost_price' => 4.50,
                'sku' => 'LANCHE-002',
                'is_available' => 1,
                'preparation_time_minutes' => 10,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => (string) Str::uuid(),
                'establishment_id' => $estId,
                'category_id' => $foodCategoryId,
                'name' => 'Refrigerante lata',
                'price' => 6.00,
                'cost_price' => 3.00,
                'sku' => 'BEBIDA-001',
                'is_available' => 1,
                'preparation_time_minutes' => 0,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);

        DB::table('inventory_items')->insert([
            'id' => (string) Str::uuid(),
            'establishment_id' => $estId,
            'name' => 'Batata congelada',
            'unit' => 'kg',
            'category' => 'Alimentos',
            'stock_quantity' => 10.000,
            'min_stock' => 2.000,
            'cost_price' => 12.50,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
}
