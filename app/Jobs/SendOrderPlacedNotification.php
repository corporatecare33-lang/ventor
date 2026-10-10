<?php

namespace App\Jobs;

use App\Mail\OrderPlaced;
use App\Models\Order;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

/**
 * Sends the order confirmation email on the queue so checkout stays fast.
 * SMS is not connected yet: add an SMS provider (e.g. SSL Wireless, BulkSMSBD)
 * here once credentials are available in .env (SMS_API_KEY, SMS_SENDER_ID).
 */
class SendOrderPlacedNotification implements ShouldQueue
{
    use Queueable;

    public int $tries = 3;

    public function __construct(public int $orderId) {}

    public function handle(): void
    {
        $order = Order::with('items')->find($this->orderId);

        if (! $order) {
            return;
        }

        if ($order->email) {
            Mail::to($order->email)->send(new OrderPlaced($order));
        }

        if ($admin = setting('order_notification_email')) {
            Mail::to($admin)->send(new OrderPlaced($order, forAdmin: true));
        }

        Log::info("Order {$order->order_number} placed", ['total' => $order->total]);
    }
}
