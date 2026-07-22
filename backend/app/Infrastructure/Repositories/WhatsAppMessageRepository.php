<?php

namespace App\Infrastructure\Repositories;

use App\Domains\WhatsApp\WhatsAppMessage;

class WhatsAppMessageRepository
{
    public function __construct(protected WhatsAppMessage $model) {}

    public function create(array $data)
    {
        return $this->model->create($data);
    }

    public function all()
    {
        return $this->model->with('customer')->get();
    }

    public function getByCustomer(int|string $customerId)
    {
        return $this->model->where('customer_id', $customerId)->orderBy('created_at', 'asc')->get();
    }
}
