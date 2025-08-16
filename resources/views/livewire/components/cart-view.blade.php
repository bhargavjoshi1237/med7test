<div class="max-w-screen-xl px-4 py-12 mx-auto space-y-12 sm:px-6 lg:px-8">
    <div class="bg-white rounded-lg ">
        <!-- Header -->
        <div class="border-b border-gray-200 px-6 py-4">
            <div class="flex items-center justify-between">
                <h1 class="text-2xl font-bold text-gray-900">Shopping Cart</h1>
                @if($this->cart && count($lines) > 0)
                    <button wire:click="clearCart" 
                            wire:confirm="Are you sure you want to clear your cart?"
                            class="text-sm text-red-600 hover:text-red-800 font-medium">
                        Clear Cart
                    </button>
                @endif
            </div>
        </div>

        <!-- Flash Messages -->
        @if (session()->has('success'))
            <div class="mx-6 mt-4 p-4 bg-green-50 border border-green-200 rounded-md">
                <div class="flex">
                    <div class="flex-shrink-0">
                        <svg class="h-5 w-5 text-green-400" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                        </svg>
                    </div>
                    <div class="ml-3">
                        <p class="text-sm font-medium text-green-800">
                            {{ session('success') }}
                        </p>
                    </div>
                </div>
            </div>
        @endif

        @if (session()->has('error'))
            <div class="mx-6 mt-4 p-4 bg-red-50 border border-red-200 rounded-md">
                <div class="flex">
                    <div class="flex-shrink-0">
                        <svg class="h-5 w-5 text-red-400" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
                        </svg>
                    </div>
                    <div class="ml-3">
                        <p class="text-sm font-medium text-red-800">
                            {{ session('error') }}
                        </p>
                    </div>
                </div>
            </div>
        @endif

        <!-- Cart Content -->
        <div class="px-6 py-4">
            @if ($this->cart && count($lines) > 0)
                <!-- Cart Items -->
                <div class="space-y-4">
                    @foreach ($lines as $index => $line)
                        <div class="flex items-center space-x-4 p-4 border border-gray-200 rounded-lg"
                             wire:key="cart_line_{{ $line['id'] }}">
                            
                            <!-- Product Image -->
                            @if($line['thumbnail'])
                                <div class="flex-shrink-0">
                                    <img class="h-20 w-20 object-cover rounded-lg" 
                                         src="{{ $line['thumbnail'] }}" 
                                         alt="{{ $line['description'] }}">
                                </div>
                            @endif

                            <!-- Product Details -->
                            <div class="flex-1 min-w-0">
                                <h3 class="text-lg font-medium text-gray-900 truncate">
                                    {{ $line['description'] }}
                                </h3>
                                <p class="text-sm text-gray-500 mt-1">
                                    SKU: {{ $line['identifier'] }}
                                </p>
                                @if($line['options'])
                                    <p class="text-sm text-gray-500">
                                        Options: {{ $line['options'] }}
                                    </p>
                                @endif
                                <p class="text-sm font-medium text-gray-900 mt-2">
                                    {{ $line['unit_price'] }} each
                                </p>
                            </div>

                            <!-- Quantity Controls -->
                            <div class="flex items-center space-x-2">
                                <button wire:click="decreaseQuantity('{{ $line['id'] }}')"
                                        class="w-8 h-8 rounded-full border border-gray-300 flex items-center justify-center hover:bg-gray-50 disabled:opacity-50 disabled:cursor-not-allowed"
                                        {{ $line['quantity'] <= 1 ? 'disabled' : '' }}>
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 12H4"></path>
                                    </svg>
                                </button>

                                <input type="number" 
                                       value="{{ $line['quantity'] }}"
                                       wire:change="updateQuantity('{{ $line['id'] }}', $event.target.value)"
                                       class="w-16 text-center border border-gray-300 rounded-md py-1 text-sm"
                                       min="1" 
                                       max="10000">

                                <button wire:click="increaseQuantity('{{ $line['id'] }}')"
                                        class="w-8 h-8 rounded-full border border-gray-300 flex items-center justify-center hover:bg-gray-50">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                                    </svg>
                                </button>
                            </div>

                            <!-- Line Total -->
                            <div class="text-right">
                                <p class="text-lg font-semibold text-gray-900">
                                    {{ $line['sub_total'] }}
                                </p>
                            </div>

                            <!-- Remove Button -->
                            <button wire:click="removeLine('{{ $line['id'] }}')"
                                    wire:confirm="Are you sure you want to remove this item?"
                                    class="text-red-600 hover:text-red-800 p-2">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                </svg>
                            </button>
                        </div>
                    @endforeach
                </div>

                <!-- Cart Summary -->
                <div class="mt-8 border-t border-gray-200 pt-6">
                    <div class="bg-gray-50 rounded-lg p-6">
                        <div class="space-y-3">
                            <div class="flex justify-between text-base font-medium text-gray-900">
                                <p>Subtotal</p>
                                <p>{{ $this->cart->subTotal->formatted() }}</p>
                            </div>
                            <div class="flex justify-between text-sm text-gray-600">
                                <p>Items in cart</p>
                                <p>{{ count($lines) }}</p>
                            </div>
                        </div>

                        <!-- Action Buttons -->
                        <div class="mt-6 space-y-3">
                            <!-- <a href="{{ route('checkout.view') }}"
                               wire:navigate
                               class="block w-full bg-blue-600 text-white py-3 px-4 rounded-lg font-medium text-center hover:bg-blue-700 transition-colors">
                                Checkout (Lunar)
                            </a> -->

                            <a href="{{ route('checkoutnew.view') }}"
                               wire:navigate
                               class="block w-full bg-blue-600 text-white py-3 px-4 rounded-lg font-medium text-center hover:bg-blue-700 transition-colors">
                                Checkout
                            </a>

                            <a href="{{ url('/') }}"
                               class="block w-full text-center text-gray-600 hover:text-gray-800 font-medium py-2">
                                Continue Shopping
                            </a>
                        </div>
                    </div>
                </div>

            @else
                <!-- Empty Cart -->
                <div class="text-center py-12">
                    <svg class="mx-auto h-24 w-24 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"></path>
                    </svg>
                    <h3 class="mt-4 text-lg font-medium text-gray-900">Your cart is empty</h3>
                    <p class="mt-2 text-gray-500">Start adding some items to your cart!</p>
                    <div class="mt-6">
                        <a href="{{ url('/') }}"
                           class="inline-flex items-center px-6 py-3 border border-transparent text-base font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700">
                            Continue Shopping
                        </a>
                    </div>
                </div>
            @endif
        </div>
    </div>
</div>
