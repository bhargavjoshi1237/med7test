<div>
    <div class="max-w-screen-xl px-4 py-12 mx-auto sm:px-6 lg:px-8">
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

                       

                        <div class="flex flex-wrap py-4">
                            <dt class="w-1/2 font-medium">
                                Total (After Discounts)
                            </dt>

                            <dd class="w-1/2 text-right">
                                {{ $cart->total->formatted() }}
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

                <!-- @include('partials.checkout.payment', [
                    'step' => $steps['payment'],
                ]) -->
            </div>
        </div>
        <!-- Move the button here, outside the grid, so it's always visible -->
        <div class="mt-12 flex justify-center">
            <form action="{{ route('payment') }}" method="get">
                <button type="submit" class="px-8 py-4 bg-green-600 text-white rounded-xl text-xl font-bold shadow hover:bg-green-700 transition">
                    Pay by Card
                </button>
            </form>
        </div>
    </div>
</div>
