<?php

namespace Database\Factories;

use App\Domains\Auth\User\User;
use App\Domains\CashRegister\CashRegister;
use Illuminate\Database\Eloquent\Factories\Factory;

class CashRegisterFactory extends Factory
{
    protected $model = CashRegister::class;

    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'status' => 'OPEN',
            'opening_balance' => 100,
            'current_balance' => 100,
            'opened_at' => now(),
            'closed_at' => null,
        ];
    }
}
