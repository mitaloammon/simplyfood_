<?php

namespace Database\Factories;

use App\Domains\CashRegister\CashRegister;
use App\Domains\CashRegister\CashTransaction;
use App\Domains\Auth\User\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class CashTransactionFactory extends Factory
{
    protected $model = CashTransaction::class;

    public function definition(): array
    {
        return [
            'cash_register_id' => CashRegister::factory(),
            'user_id' => User::factory(),
            'type' => 'RECEBIMENTO',
            'amount' => 50,
            'description' => $this->faker->sentence(),
            'metadata' => ['source' => 'factory'],
        ];
    }
}
