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
    }
}
