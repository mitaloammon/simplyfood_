<?php

namespace App\Infrastructure\Repositories;

use App\Domains\Payment\PaymentTransaction;

class PaymentTransactionRepository
{
    public function __construct(protected PaymentTransaction $model) {}

    public function create(array $data)
    {
        return $this->model->create($data);
    }

    public function find(int|string $id)
    {
        return $this->model->find($id);
    }

    public function findByTransactionId(string $transactionId)
    {
        return $this->model->where('transaction_id', $transactionId)->first();
    }

    public function update(int|string $id, array $data)
    {
        $record = $this->find($id);
        if ($record) {
            $record->update($data);
        }
        return $record;
    }
}
