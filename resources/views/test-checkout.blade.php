<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Test New Checkout</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100">
    <div class="container mx-auto py-8">
        <div class="max-w-md mx-auto bg-white rounded-lg shadow-md p-6">
            <h1 class="text-2xl font-bold mb-6 text-center">Test New Checkout</h1>
            
            <div class="space-y-4">
                <a href="{{ route('checkoutnew.view') }}" 
                   class="block w-full bg-green-600 text-white text-center py-3 px-4 rounded-lg hover:bg-green-700 transition-colors">
                    Go to New Checkout Page
                </a>
                
                <a href="{{ route('checkout.view') }}" 
                   class="block w-full bg-blue-600 text-white text-center py-3 px-4 rounded-lg hover:bg-blue-700 transition-colors">
                    Go to Original Checkout Page
                </a>
                
                <a href="{{ url('/') }}" 
                   class="block w-full bg-gray-600 text-white text-center py-3 px-4 rounded-lg hover:bg-gray-700 transition-colors">
                    Back to Home
                </a>
            </div>
            
            <div class="mt-8 p-4 bg-blue-50 rounded-lg">
                <h3 class="font-semibold text-blue-800 mb-2">New Checkout Features:</h3>
                <ul class="text-sm text-blue-700 space-y-1">
                    <li>• Uses custom Stripe implementation</li>
                    <li>• Creates orders directly in Lunar tables</li>
                    <li>• Handles cart-to-order conversion</li>
                    <li>• Manages transactions and payment intents</li>
                    <li>• Accessible at /checkoutnew</li>
                </ul>
            </div>
        </div>
    </div>
</body>
</html>