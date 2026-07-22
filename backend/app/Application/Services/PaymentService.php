<?php

namespace App\Application\Services;

use App\Infrastructure\ExternalServices\Payment\PaymentGatewayInterface;
use App\Domains\Payment\PaymentTransaction;
use App\Domains\Order\Order;
use Exception;

class PaymentService extends BaseService
{
    protected string $modelClass = PaymentTransaction::class;

    public function __construct(protected PaymentGatewayInterface $gateway) {}

    /**
     * Process order payment.
     */
    public function process(array $data): PaymentTransaction
    {
        $orderId = $data['order_id'] ?? null;
        if (!$orderId) {
            throw new Exception("Order ID is required to process payment.");
        }

        $order = Order::findOrFail($orderId);
        
        // Prepare charge data
        $chargeData = [
            'order_id' => $order->id,
            'amount' => $order->total,
            'description' => "Order #{$order->id} payment"
        ];

        // Call the gateway interface
        $response = $this->gateway->createCharge($chargeData);

        // Record the transaction
        $transaction = PaymentTransaction::create([
            'order_id' => $order->id,
            'gateway' => $response['gateway'],
            'transaction_id' => $response['transaction_id'],
            'status' => $response['payment_status'],
            'amount' => $response['amount'],
            'payment_method' => $data['payment_method'] ?? 'PIX'
        ]);

        // If payment approved, update order status
        if ($response['payment_status'] === 'APPROVED') {
            $order->status = 'PAID';
            $order->save();
        }

        return $transaction;
    }
}
