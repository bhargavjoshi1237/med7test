<?php

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use Lunar\Models\Order;
use Lunar\Base\ValueObjects\Cart\TaxBreakdown;
use Illuminate\Support\Facades\Log;

echo "Testing Order creation with observer...\n";

try {
    // Create an empty TaxBreakdown value object
    $taxBreakdown = new TaxBreakdown();
    
    $order = Order::create([
        'user_id' => null,
        'channel_id' => 1,
        'status' => 'test',
        'reference' => 'TEST-' . time(),
        'sub_total' => 1000,
        'discount_total' => 0,
        'shipping_total' => 0,
        'tax_breakdown' => $taxBreakdown,
        'tax_total' => 0,
        'total' => 1000,
        'currency_code' => 'USD',
        'exchange_rate' => 1,
        'placed_at' => now(),
        'cart_id' => 1, // assuming cart 1 exists
        'discount_breakdown' => [],
        'shipping_breakdown' => [],
        'customer_id' => null,
        'new_customer' => 1,
    ]);
    
    echo "Order created successfully with ID: " . $order->id . "\n";
    echo "Check the logs for observer output.\n";
    
} catch (\Exception $e) {
    echo "Error creating order: " . $e->getMessage() . "\n";
}