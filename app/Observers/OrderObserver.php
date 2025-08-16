<?php

namespace App\Observers;

use Lunar\Models\Order;
use Illuminate\Support\Facades\Log;

class OrderObserver
{
    public function created(Order $order)
    {
        Log::debug('OrderObserver: Order created', ['order_id' => $order->id]);
        // Commission tracking has been moved to CheckoutNewPage
        // This observer can be used for other order creation logic if needed
    }

    public function updated(Order $order)
    {
        Log::debug('OrderObserver: Order updated', [
            'order_id' => $order->id,
            'status' => $order->status,
        ]);
        // Commission tracking has been moved to CheckoutNewPage
        // This observer can be used for other order update logic if needed
    }
}
