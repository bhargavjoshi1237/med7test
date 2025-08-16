<div>
    <div class="max-w-screen-xl px-4 py-12 mx-auto sm:px-6 lg:px-8">
        @if (session('error'))
            <div class="mb-6 p-4 bg-red-100 border border-red-400 text-red-700 rounded">
                {{ session('error') }}
            </div>
        @endif

        <div class="grid grid-cols-1 gap-8 lg:grid-cols-3 lg:items-start">
            <div class="px-6 py-8 space-y-4 bg-white border border-gray-100 lg:sticky lg:top-8 rounded-xl lg:order-last">
                <h3 class="font-medium">
                    Order Summary
                </h3>

                <form wire:submit.prevent="applyCoupon" class="mb-6">
                    <label for="coupon" class="block text-sm font-medium text-gray-700">Coupon Code</label>
                    <div class="flex mt-1">
                        <input type="text" id="coupon" wire:model.defer="couponCode" class="flex-1 px-2 py-1 border rounded-l"
                               placeholder="Enter coupon code">
                        <button type="submit" class="px-4 py-1 bg-blue-600 text-white rounded-r">Apply</button>
                    </div>
                    @if ($couponError)
                        <p class="text-xs text-red-600 mt-1">{{ $couponError }}</p>
                    @endif
                    @if ($appliedCoupon)
                        <p class="text-xs text-green-600 mt-1">Coupon "{{ $appliedCoupon }}" applied!</p>
                    @endif
                </form>
                <br>
                <div class="flow-root">
                    <div class="-my-4 divide-y divide-gray-100">
                        @foreach ($cart->lines as $line)
                            <div class="flex items-center py-4"
                                 wire:key="cart_line_{{ $line->id }}">
                                <img class="object-cover w-16 h-16 rounded"
                                     src="{{ $line->purchasable->getThumbnail()->getUrl() }}" />

                                <div class="flex-1 ml-4">
                                    <p class="text-sm font-medium max-w-[35ch]">
                                        {{ $line->purchasable->getDescription() }}
                                    </p>

                                    <span class="block mt-1 text-xs text-gray-500">
                                        {{ $line->quantity }} @ {{ $line->subTotal->formatted() }}
                                    </span>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>

                <div class="flow-root">
                    <dl class="-my-4 text-sm divide-y divide-gray-100">
                        <div class="flex flex-wrap py-4">
                            <dt class="w-1/2 font-medium">
                                Sub Total
                            </dt>

                            <dd class="w-1/2 text-right">
                                {{ $cart->subTotal->formatted() }}
                            </dd>
                        </div>

                        @if ($this->shippingOption)
                            <div class="flex flex-wrap py-4">
                                <dt class="w-1/2 font-medium">
                                    Shipping
                                    {{ $this->shippingOption->getDescription() }}
                                </dt>

                                <dd class="w-1/2 text-right">
                                    {{ $this->shippingOption->getPrice()->formatted() }}
                                </dd>
                            </div>
                        @endif

                        @foreach ($cart->taxBreakdown->amounts as $tax)
                            <div class="flex flex-wrap py-4">
                                <dt class="w-1/2 font-medium">
                                    {{ $tax->description }}
                                </dt>
                                <dd class="w-1/2 text-right">
                                    {{ $tax->price->formatted() }}
                                </dd>
                            </div>
                        @endforeach

                        @if ($cart->discountTotal && $cart->discountTotal->value > 0)
                            <div class="flex flex-wrap py-4">
                                <dt class="w-1/2 font-medium text-green-600">
                                    Discount
                                </dt>
                                <dd class="w-1/2 text-right text-green-600">
                                    -{{ $cart->discountTotal->formatted() }}
                                </dd>
                            </div>
                        @endif

                        <div class="flex flex-wrap py-4 border-t border-gray-200">
                            <dt class="w-1/2 font-bold text-lg">
                                Total
                            </dt>

                            <dd class="w-1/2 text-right font-bold text-lg">
                                @php
                                    $totalWithTax = $cart->subTotal->value + 
                                                   ($cart->taxTotal->value ?? 0) + 
                                                   ($cart->shippingTotal->value ?? 0) - 
                                                   ($cart->discountTotal->value ?? 0);
                                    $currency = $cart->currency ?? (object)['code' => 'USD'];
                                    $formatted = $currency->code === 'USD' 
                                        ? '$' . number_format($totalWithTax / 100, 2)
                                        : number_format($totalWithTax / 100, 2) . ' ' . $currency->code;
                                @endphp
                                {{ $formatted }}
                            </dd>
                        </div>
                    </dl>
                </div>
            </div>

            <div class="space-y-6 lg:col-span-2">
                @include('partials.checkout.address', [
                    'type' => 'shipping',
                    'step' => $steps['shipping_address'],
                ])

                @include('partials.checkout.shipping_option', [
                    'step' => $steps['shipping_option'],
                ])

                @include('partials.checkout.address', [
                    'type' => 'billing',
                    'step' => $steps['billing_address'],
                ])

                <!-- Custom Payment Section -->
                <div class="p-6 bg-white border border-gray-100 rounded-xl">
                    <div class="flex items-center justify-between">
                        <h2 class="text-lg font-medium">
                            Payment
                        </h2>
                        <span class="px-3 py-1 text-xs font-medium text-white bg-gray-900 rounded-full">
                            Step {{ $steps['payment'] }}
                        </span>
                    </div>

                    @if ($currentStep >= $steps['payment'])
                        <div class="mt-6">
                            <div class="p-4 bg-blue-50 border border-blue-200 rounded-lg mb-4">
                                <div class="flex items-center">
                                    <svg class="w-5 h-5 text-blue-600 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"></path>
                                    </svg>
                                    <span class="text-sm font-medium text-blue-800">Secure Card Payment</span>
                                </div>
                                <p class="text-xs text-blue-600 mt-1">Your payment will be processed securely through Stripe</p>
                            </div>

                            <button 
                                wire:click="initiateStripePayment" 
                                class="w-full px-6 py-4 bg-green-600 text-white rounded-xl text-lg font-bold shadow hover:bg-green-700 transition-colors duration-200 flex items-center justify-center"
                                wire:loading.attr="disabled"
                                wire:loading.class="opacity-50 cursor-not-allowed"
                            >
                                <span wire:loading.remove>
                                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path>
                                    </svg>
                                    @php
                                        $totalWithTax = $cart->subTotal->value + 
                                                       ($cart->taxTotal->value ?? 0) + 
                                                       ($cart->shippingTotal->value ?? 0) - 
                                                       ($cart->discountTotal->value ?? 0);
                                        $currency = $cart->currency ?? (object)['code' => 'USD'];
                                        $formatted = $currency->code === 'USD' 
                                            ? '$' . number_format($totalWithTax / 100, 2)
                                            : number_format($totalWithTax / 100, 2) . ' ' . $currency->code;
                                    @endphp
                                    Pay Securely - {{ $formatted }}
                                </span>
                                <span wire:loading>
                                    <svg class="animate-spin -ml-1 mr-3 h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                    </svg>
                                    Processing...
                                </span>
                            </button>

                            <div class="mt-4 flex items-center justify-center space-x-4 text-xs text-gray-500">
                                <div class="flex items-center">
                                    <svg class="w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M5 9V7a5 5 0 0110 0v2a2 2 0 012 2v5a2 2 0 01-2 2H5a2 2 0 01-2-2v-5a2 2 0 012-2zm8-2v2H7V7a3 3 0 016 0z" clip-rule="evenodd"></path>
                                    </svg>
                                    SSL Secured
                                </div>
                                <div class="flex items-center">
                                    <span class="font-semibold">Stripe</span>
                                    <span class="ml-1">Powered</span>
                                </div>
                            </div>
                        </div>
                    @else
                        <div class="mt-6 p-4 bg-gray-50 border border-gray-200 rounded-lg">
                            <p class="text-sm text-gray-600">
                                Please complete the previous steps to proceed with payment.
                            </p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>