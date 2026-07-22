<?php

namespace App\Infrastructure\ExternalServices\Payment;

interface PaymentGatewayInterface
{
    /**
     * Create a charge on the external gateway.
     */
    public function createCharge(array $data): array;
}
