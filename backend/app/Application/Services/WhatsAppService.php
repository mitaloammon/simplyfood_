<?php

namespace App\Application\Services;

use App\Domains\WhatsApp\WhatsAppMessage;

class WhatsAppService extends BaseService
{
    protected string $modelClass = WhatsAppMessage::class;

    /**
     * Send simulated message.
     */
    public function sendMessage(array $data): WhatsAppMessage
    {
        $message = WhatsAppMessage::create([
            'customer_id' => $data['customer_id'] ?? null,
            'direction' => 'OUTBOUND',
            'message' => $data['message'],
            'status' => 'SENT'
        ]);

        // Simulate delivery delay
        $message->status = 'DELIVERED';
        $message->save();

        return $message;
    }
}
