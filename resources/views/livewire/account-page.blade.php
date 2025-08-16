<div class="max-w-screen-xl px-4 flex mx-auto space-y-12 sm:px-6 lg:px-8">
    <!-- Sidebar -->
    <div class="w-64 bg-gray-100 p-4">
        <nav class="space-y-2 ">
            {{-- <a href="#" class="block py-2 text-red-600 font-medium">Dashboard</a> --}}
            <a href="#" class="block py-2 text-red-600 font-medium">Orders</a>
            {{-- <a href="#" class="block py-2 text-gray-800 hover:text-red-600">Downloads</a>
            <a href="#" class="block py-2 text-gray-800 hover:text-red-600">Addresses</a>
            <a href="#" class="block py-2 text-gray-800 hover:text-red-600">Payment methods</a> --}}
            <a href="#" class="block py-2 text-gray-800 hover:text-red-600">Account details</a>
            {{-- <a href="#" class="block py-2 text-gray-800 hover:text-red-600">Log out</a> --}}
        </nav>
    </div>

    <!-- Main Content -->
    <div class="flex-1 p-6">
        <h1 class="text-2xl font-bold mb-6">Orders</h1>

        @if($orders->isEmpty())
            <div class="bg-white rounded-lg shadow p-8 text-center">
                <p class="text-gray-500">No orders found. Start shopping to see your orders here!</p>
                <a href="{{ url('/') }}" 
                   wire:navigate
                   class="mt-4 inline-block bg-red-600 text-white px-6 py-2 rounded-md hover:bg-red-700">
                    Continue Shopping
                </a>
            </div>
        @else
            <div class="bg-white rounded-lg shadow overflow-hidden">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Order</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Total</th>
                            <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @foreach($orders as $order)
                            <tr>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="font-medium text-red-600">
                                        #{{ is_array($order) ? ($order['reference'] ?? '') : ($order->reference ?? '') }}
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    {{ \Carbon\Carbon::parse(is_array($order) ? ($order['placed_at'] ?? '') : ($order->placed_at ?? ''))->format('F j, Y') }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">
                                        {{ is_array($order) ? ($order['status'] ?? '') : ($order->status ?? '') }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    @php
                                        $total = is_array($order)
                                            ? (isset($order['total']['value']) ? $order['total']['value'] : 0)
                                            : (isset($order->total) ? $order->total->value : 0);
                                    @endphp
                                    USD$ {{ number_format($total / 100, 2) }} for {{ rand(1,5) }} items
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                    <a href="#" class="text-red-600 hover:text-red-900 mr-3">View</a>
                                    <a href="#" class="text-red-600 hover:text-red-900">Invoice</a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>
</div>