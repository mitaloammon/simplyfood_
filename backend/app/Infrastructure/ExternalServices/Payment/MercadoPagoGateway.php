<?php

namespace App\Infrastructure\ExternalServices\Payment;

class MercadoPagoGateway implements PaymentGatewayInterface
{
    public function createCharge(array $data): array
    {
        // Simulate an external gateway call (MercadoPago API)
        $amount = $data['amount'] ?? 0;
        $orderId = $data['order_id'] ?? 0;

        return [
            'status' => 'success',
            'gateway' => 'MERCADOPAGO',
            'transaction_id' => 'mp_' . uniqid(),
            'payment_status' => 'APPROVED', // Simulating instant approval (PIX/Credit Card)
            'amount' => $amount,
            'qr_code' => '00020126580014br.gov.bcb.pix0136' . str_replace('-', '', uniqid()) . '5204000053039865405' . number_format($amount, 2, '.', '') . '5802BR5913SIMPLYFOOD6009SAOPAULO62070503***6304',
            'qr_code_base64' => 'iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mNkYAAAAAYAAjCB0C8AAAAASUVORK5CYII=', // Mock base64 image
            'message' => 'Simulated charge processed successfully.'
        ];
    }
}
