<?php

namespace App\Livewire;

use Illuminate\Support\Collection;
use Illuminate\View\View;
use Livewire\Component;
use Lunar\Facades\CartSession;
use Lunar\Facades\ShippingManifest;
use Lunar\Models\Cart;
use Lunar\Models\CartAddress;
use Lunar\Models\Country;
use Illuminate\Support\Facades\DB;
use Stripe\Stripe;
use Stripe\Checkout\Session as StripeSession;
use Stripe\PaymentIntent;
use Stripe\Charge;
use App\Models\Affiliate;
use App\Services\AffiliateCommissionService;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class CheckoutNewPage extends Component
{
    /**
     * The Cart instance.
     */
    public ?Cart $cart;

    /**
     * The shipping address instance.
     */
    public ?CartAddress $shipping = null;

    /**
     * The billing address instance.
     */
    public ?CartAddress $billing = null;

    /**
     * The current checkout step.
     */
    public int $currentStep = 1;

    /**
     * Whether the shipping address is the billing address too.
     */
    public bool $shippingIsBilling = true;

    /**
     * The chosen shipping option.
     */
    public $chosenShipping = null;

    /**
     * The checkout steps.
     */
    public array $steps = [
        'shipping_address' => 1,
        'shipping_option' => 2,
        'billing_address' => 3,
        'payment' => 4,
    ];

    /**
     * {@inheritDoc}
     */
    protected $listeners = [
        'cartUpdated' => 'refreshCart',
        'selectedShippingOption' => 'refreshCart',
    ];

    // Coupon code properties
    public $couponCode = '';
    public $couponError = '';
    public $appliedCoupon = '';

    /**
     * {@inheritDoc}
     */
    public function rules(): array
    {
        return array_merge(
            $this->getAddressValidation('shipping'),
            $this->getAddressValidation('billing'),
            [
                'shippingIsBilling' => 'boolean',
                'chosenShipping' => 'required',
            ]
        );
    }

    public function mount(): void
    {
        if (! $this->cart = CartSession::current()) {
            $this->redirect('/');
            return;
        }

        // Handle Stripe success callback
        if (request()->query('stripe_success') && request()->query('session_id')) {
            $this->handleStripeSuccess(request()->query('session_id'));
            return;
        }

        $this->shipping = $this->cart->shippingAddress ?: new CartAddress;
        $this->billing = $this->cart->billingAddress ?: new CartAddress;
        $this->determineCheckoutStep();
    }

    public function hydrate(): void
    {
        $this->cart = CartSession::current();
    }

    /**
     * Trigger an event to refresh addresses.
     */
    public function triggerAddressRefresh(): void
    {
        $this->dispatch('refreshAddress');
    }

    /**
     * Determines what checkout step we should be at.
     */
    public function determineCheckoutStep(): void
    {
        $shippingAddress = $this->cart->shippingAddress;
        $billingAddress = $this->cart->billingAddress;

        if ($shippingAddress) {
            if ($shippingAddress->id) {
                $this->currentStep = $this->steps['shipping_address'] + 1;
            }

            // Do we have a selected option?
            if ($this->shippingOption) {
                $this->chosenShipping = $this->shippingOption->getIdentifier();
                $this->currentStep = $this->steps['shipping_option'] + 1;
            } else {
                $this->currentStep = $this->steps['shipping_option'];
                if ($this->shippingOptions->isNotEmpty()) {
                    $this->chosenShipping = $this->shippingOptions->first()?->getIdentifier();
                }
                return;
            }
        }

        if ($billingAddress) {
            $this->currentStep = $this->steps['billing_address'] + 1;
        }
    }

    /**
     * Refresh the cart instance.
     */
    public function refreshCart(): void
    {
        $this->cart = CartSession::current();
    }

    /**
     * Return the shipping option.
     */
    public function getShippingOptionProperty()
    {
        $shippingAddress = $this->cart->shippingAddress;

        if (! $shippingAddress) {
            return;
        }

        if ($option = $shippingAddress->shipping_option) {
            return ShippingManifest::getOptions($this->cart)->first(function ($opt) use ($option) {
                return $opt->getIdentifier() == $option;
            });
        }

        return null;
    }

    /**
     * Save the address for a given type.
     */
    public function saveAddress(string $type): void
    {
        $validatedData = $this->validate(
            $this->getAddressValidation($type)
        );

        $address = $this->{$type};

        if ($type == 'billing') {
            $this->cart->setBillingAddress($address);
            $this->billing = $this->cart->billingAddress;

            // Recalculate cart to apply correct tax based on billing address
            $this->cart->calculate();
        }

        if ($type == 'shipping') {
            $this->cart->setShippingAddress($address);
            $this->shipping = $this->cart->shippingAddress;

            if ($this->shippingIsBilling) {
                // Do we already have a billing address?
                if ($billing = $this->cart->billingAddress) {
                    $billing->fill($validatedData['shipping']);
                    $this->cart->setBillingAddress($billing);
                } else {
                    $address = $address->only(
                        $address->getFillable()
                    );
                    $this->cart->setBillingAddress($address);
                }

                $this->billing = $this->cart->billingAddress;
            }

            // Recalculate cart to apply correct tax based on shipping address
            $this->cart->calculate();
        }

        $this->determineCheckoutStep();
    }

    /**
     * Save the selected shipping option.
     */
    public function saveShippingOption(): void
    {
        $option = $this->shippingOptions->first(fn($option) => $option->getIdentifier() == $this->chosenShipping);

        CartSession::setShippingOption($option);

        $this->refreshCart();

        $this->determineCheckoutStep();
    }

    /**
     * Initiate payment using custom Stripe implementation
     */
    public function initiateStripePayment()
    {
        try {
            Stripe::setApiKey(env('STRIPE_SECRET'));

            // Build description from cart lines
            $description = $this->cart->lines->count()
                ? implode(', ', $this->cart->lines->map(fn($line) => $line->purchasable->getDescription())->toArray())
                : 'Order Payment';

            // Calculate the correct total including tax
            $totalWithTax = $this->cart->subTotal->value +
                ($this->cart->taxTotal->value ?? 0) +
                ($this->cart->shippingTotal->value ?? 0) -
                ($this->cart->discountTotal->value ?? 0);

            $session = StripeSession::create([
                'payment_method_types' => ['card'],
                'line_items' => [[
                    'price_data' => [
                        'currency' => strtolower($this->cart->currency ? $this->cart->currency->code : 'usd'),
                        'product_data' => [
                            'name' => $description,
                        ],
                        'unit_amount' => $totalWithTax, // Use calculated total with tax
                    ],
                    'quantity' => 1,
                ]],
                'mode' => 'payment',
                'success_url' => route('checkoutnew.view', [], true) . '?stripe_success=1&session_id={CHECKOUT_SESSION_ID}',
                'cancel_url' => route('checkoutnew.view', [], true),
                'metadata' => [
                    'cart_id' => $this->cart->id,
                    'user_id' => $this->cart->user_id ?? '',
                ],
            ]);

            return redirect($session->url);
        } catch (\Exception $e) {
            \Log::error('Stripe payment initialization failed', [
                'error' => $e->getMessage(),
                'cart_id' => $this->cart->id ?? null,
                'user_id' => $this->cart->user_id ?? null,
            ]);
            session()->flash('error', 'Payment initialization failed: ' . $e->getMessage());
            return;
        }
    }

    /**
     * Handle successful Stripe payment and create order
     */
    public function handleStripeSuccess($sessionId)
    {
        try {
            Stripe::setApiKey(env('STRIPE_SECRET'));
            $session = StripeSession::retrieve($sessionId);

            if ($session->payment_status !== 'paid') {
                session()->flash('error', 'Payment was not completed successfully.');
                return;
            }

            DB::beginTransaction();

            // Convert cart lines to order lines data
            $orderLines = [];
            foreach ($this->cart->lines as $line) {
                // Properly format tax breakdown for storage
                $taxBreakdown = [];
                if ($line->taxBreakdown && is_object($line->taxBreakdown) && $line->taxBreakdown->amounts && $line->taxBreakdown->amounts->isNotEmpty()) {
                    foreach ($line->taxBreakdown->amounts as $amount) {
                        $taxBreakdown[] = [
                            'description' => $amount->description ?? '',
                            'identifier' => $amount->identifier ?? '',
                            'percentage' => $amount->percentage ?? 0,
                            'value' => $amount->price->value ?? 0,
                            'currency_code' => $amount->price->currency ? $amount->price->currency->code : 'USD',
                        ];
                    }
                }

                $orderLines[] = [
                    'purchasable_type' => get_class($line->purchasable),
                    'purchasable_id' => $line->purchasable->id,
                    'type' => 'physical', // or 'digital' based on your needs
                    'description' => $line->purchasable->getDescription(),
                    'option' => null,
                    'identifier' => $line->purchasable->getIdentifier(),
                    'unit_price' => $line->unitPrice->value,
                    'unit_quantity' => $line->quantity,
                    'quantity' => $line->quantity,
                    'sub_total' => $line->subTotal->value,
                    'discount_total' => $line->discountTotal->value ?? 0,
                    'tax_breakdown' => json_encode($taxBreakdown),
                    'tax_total' => $line->taxTotal->value ?? 0,
                    'total' => $line->total->value,
                    'notes' => null,
                    'meta' => json_encode($line->meta ?? []),
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }

            // Properly format tax breakdown for order storage
            $orderTaxBreakdown = [];
            \Log::info('Processing cart tax breakdown', [
                'cart_id' => $this->cart->id,
                'has_tax_breakdown' => isset($this->cart->taxBreakdown),
                'tax_breakdown_type' => gettype($this->cart->taxBreakdown ?? null),
                'tax_total' => $this->cart->taxTotal->value ?? 0,
            ]);

            if ($this->cart->taxBreakdown && is_object($this->cart->taxBreakdown) && $this->cart->taxBreakdown->amounts && $this->cart->taxBreakdown->amounts->isNotEmpty()) {
                foreach ($this->cart->taxBreakdown->amounts as $amount) {
                    $taxItem = [
                        'description' => $amount->description ?? '',
                        'identifier' => $amount->identifier ?? '',
                        'percentage' => $amount->percentage ?? 0,
                        'value' => $amount->price->value ?? 0,
                        'currency_code' => $amount->price->currency ? $amount->price->currency->code : 'USD',
                    ];
                    $orderTaxBreakdown[] = $taxItem;

                    \Log::info('Added tax breakdown item', [
                        'cart_id' => $this->cart->id,
                        'tax_item' => $taxItem,
                    ]);
                }
            } else {
                // If no detailed breakdown available but we have tax total, create a generic entry
                if (($this->cart->taxTotal->value ?? 0) > 0) {
                    $genericTaxItem = [
                        'description' => 'Tax',
                        'identifier' => 'TAX',
                        'percentage' => 0, // We don't know the percentage without breakdown
                        'value' => $this->cart->taxTotal->value,
                        'currency_code' => $this->cart->currency ? $this->cart->currency->code : 'USD',
                    ];
                    $orderTaxBreakdown[] = $genericTaxItem;

                    \Log::info('Added generic tax breakdown item', [
                        'cart_id' => $this->cart->id,
                        'tax_item' => $genericTaxItem,
                    ]);
                }
            }

            // Properly format discount breakdown for order storage
            $orderDiscountBreakdown = [];
            if ($this->cart->discountBreakdown && is_object($this->cart->discountBreakdown) && isset($this->cart->discountBreakdown->amounts)) {
                foreach ($this->cart->discountBreakdown->amounts as $amount) {
                    // Get the discount ID from the discount object if available
                    $discountId = null;
                    if (isset($amount->discount) && is_object($amount->discount)) {
                        $discountId = $amount->discount->id ?? null;
                    }

                    $orderDiscountBreakdown[] = [
                        'description' => $amount->description ?? '',
                        'identifier' => $amount->identifier ?? '',
                        'value' => $amount->price->value ?? 0,
                        'currency_code' => $amount->price->currency ? $amount->price->currency->code : 'USD',
                        'discount_id' => $discountId, // Add discount ID for affiliate tracking
                    ];
                }
            }

            // Calculate the correct total including tax
            $calculatedTotal = $this->cart->subTotal->value +
                ($this->cart->taxTotal->value ?? 0) +
                ($this->cart->shippingTotal->value ?? 0) -
                ($this->cart->discountTotal->value ?? 0);

            // Create order in lunar_orders
            $orderData = [
                'user_id' => $this->cart->user_id ?? null,
                'channel_id' => 1,
                'status' => 'payment-received',
                'reference' => $session->id,
                'customer_reference' => null,
                'sub_total' => (int) $this->cart->subTotal->value,
                'discount_total' => (int) ($this->cart->discountTotal->value ?? 0),
                'shipping_total' => (int) ($this->cart->shippingTotal->value ?? 0),
                'tax_breakdown' => json_encode($orderTaxBreakdown),
                'tax_total' => (int) ($this->cart->taxTotal->value ?? 0),
                'total' => (int) $calculatedTotal, // Use calculated total
                'notes' => null,
                'currency_code' => $this->cart->currency ? $this->cart->currency->code : 'USD',
                'compare_currency_code' => null,
                'exchange_rate' => 1.0,
                'placed_at' => now(),
                'meta' => json_encode(['stripe_session_id' => $session->id]),
                'created_at' => now(),
                'updated_at' => now(),
                'customer_id' => $this->cart->customer_id ?? null,
                'new_customer' => $this->cart->customer_id ? 0 : 1,
                'discount_breakdown' => json_encode($orderDiscountBreakdown),
                'shipping_breakdown' => json_encode($this->transformShippingBreakdown($this->cart->shippingBreakdown ?? [])),
                'cart_id' => $this->cart->id,
                'fingerprint' => null,
            ];
            \Log::info('Creating order with data', ['order_data' => $orderData]);

            try {
                $orderId = DB::table('lunar_orders')->insertGetId($orderData);
                \Log::info('Order created successfully', ['order_id' => $orderId]);
            } catch (\Exception $e) {
                \Log::error('Failed to create order', [
                    'error' => $e->getMessage(),
                    'order_data' => $orderData,
                ]);
                throw $e;
            }

            // Create order lines
            foreach ($orderLines as $lineData) {
                $lineData['order_id'] = $orderId;

                // Ensure proper data types for order line
                $lineData['unit_price'] = (int) $lineData['unit_price'];
                $lineData['sub_total'] = (int) $lineData['sub_total'];
                $lineData['discount_total'] = (int) $lineData['discount_total'];
                $lineData['tax_total'] = (int) $lineData['tax_total'];
                $lineData['total'] = (int) $lineData['total'];
                $lineData['quantity'] = (int) $lineData['quantity'];
                $lineData['unit_quantity'] = (int) $lineData['unit_quantity'];

                \Log::info('Creating order line', ['line_data' => $lineData]);

                try {
                    DB::table('lunar_order_lines')->insert($lineData);
                } catch (\Exception $e) {
                    \Log::error('Failed to create order line', [
                        'error' => $e->getMessage(),
                        'line_data' => $lineData,
                    ]);
                    throw $e;
                }
            }

            // Copy addresses to order
            if ($this->cart->shippingAddress) {
                try {
                    $shippingAddressData = $this->prepareAddressData($this->cart->shippingAddress, $orderId, 'shipping');
                    \Log::info('Shipping address data prepared', ['data' => $shippingAddressData]);
                    DB::table('lunar_order_addresses')->insert($shippingAddressData);
                } catch (\Exception $e) {
                    \Log::error('Failed to insert shipping address', [
                        'error' => $e->getMessage(),
                        'cart_address' => $this->cart->shippingAddress->toArray(),
                    ]);
                    throw $e;
                }
            }

            if ($this->cart->billingAddress) {
                try {
                    $billingAddressData = $this->prepareAddressData($this->cart->billingAddress, $orderId, 'billing');
                    \Log::info('Billing address data prepared', ['data' => $billingAddressData]);
                    DB::table('lunar_order_addresses')->insert($billingAddressData);
                } catch (\Exception $e) {
                    \Log::error('Failed to insert billing address', [
                        'error' => $e->getMessage(),
                        'cart_address' => $this->cart->billingAddress->toArray(),
                    ]);
                    throw $e;
                }
            }

            // Get the Payment Intent to retrieve the Charge ID
            $paymentIntent = PaymentIntent::retrieve($session->payment_intent);

            $chargeId = null;
            $cardType = '';
            $lastFour = null;

            // Try to get charges from the payment intent
            if ($paymentIntent->charges && $paymentIntent->charges->data && count($paymentIntent->charges->data) > 0) {
                $charge = $paymentIntent->charges->data[0];
                $chargeId = $charge->id;

                // Extract card details if available
                if (isset($charge->payment_method_details->card)) {
                    $cardDetails = $charge->payment_method_details->card;
                    $cardType = $cardDetails->brand ?? '';
                    $lastFour = $cardDetails->last4 ?? null;
                }

                \Log::info('Retrieved charge details from payment intent', [
                    'payment_intent_id' => $session->payment_intent,
                    'charge_id' => $chargeId,
                    'card_type' => $cardType,
                    'last_four' => $lastFour
                ]);
            } else {
                // For Stripe Checkout Sessions, charges might not be immediately available
                // Let's try to retrieve charges directly using the payment intent ID
                try {
                    $charges = \Stripe\Charge::all([
                        'payment_intent' => $session->payment_intent,
                        'limit' => 1
                    ]);

                    if ($charges->data && count($charges->data) > 0) {
                        $charge = $charges->data[0];
                        $chargeId = $charge->id;

                        // Extract card details if available
                        if (isset($charge->payment_method_details->card)) {
                            $cardDetails = $charge->payment_method_details->card;
                            $cardType = $cardDetails->brand ?? '';
                            $lastFour = $cardDetails->last4 ?? null;
                        }

                        \Log::info('Retrieved charge details via direct charge lookup', [
                            'payment_intent_id' => $session->payment_intent,
                            'charge_id' => $chargeId,
                            'card_type' => $cardType,
                            'last_four' => $lastFour
                        ]);
                    } else {
                        \Log::warning('No charges found via direct lookup either, using payment intent as fallback', [
                            'payment_intent_id' => $session->payment_intent,
                            'payment_intent_status' => $paymentIntent->status
                        ]);

                        // As a last resort, use the payment intent ID
                        // This will allow the order to be created, but refunds might not work
                        $chargeId = $session->payment_intent;
                    }
                } catch (\Exception $e) {
                    \Log::error('Failed to retrieve charges via direct lookup', [
                        'payment_intent_id' => $session->payment_intent,
                        'error' => $e->getMessage()
                    ]);

                    // Use payment intent as fallback
                    $chargeId = $session->payment_intent;
                }
            }

            // Determine if we have a proper charge ID or are using payment intent as fallback
            $isProperChargeId = str_starts_with($chargeId, 'ch_');
            $notes = $isProperChargeId
                ? 'Payment processed via custom Stripe integration'
                : 'Payment processed via custom Stripe integration (using payment intent ID as reference - charge ID not immediately available)';

            // Create transaction in lunar_transactions (matching Lunar's StoreCharges format)
            $transactionData = [
                'order_id' => $orderId,
                'success' => 1,
                'driver' => 'stripe',
                'amount' => (int) $calculatedTotal, // Use the same calculated total
                'reference' => $chargeId, // This is the critical field - prefer charge ID
                'status' => 'succeeded', // Use Stripe's charge status
                'notes' => $notes,
                'card_type' => $cardType,
                'last_four' => $lastFour,
                'meta' => json_encode([
                    'stripe_session_id' => $session->id,
                    'payment_intent_id' => $session->payment_intent,
                    'charge_id' => $isProperChargeId ? $chargeId : null,
                    'needs_charge_id_update' => !$isProperChargeId,
                ]),
                'created_at' => now(),
                'updated_at' => now(),
                'parent_transaction_id' => null,
                'captured_at' => now(),
                'type' => 'capture',
            ];

            \Log::info('Creating transaction with data', [
                'transaction_data' => $transactionData,
                'charge_id_format_check' => [
                    'starts_with_ch' => str_starts_with($chargeId, 'ch_'),
                    'charge_id_length' => strlen($chargeId),
                    'charge_id' => $chargeId
                ]
            ]);

            try {
                DB::table('lunar_transactions')->insert($transactionData);
                \Log::info('Transaction created successfully with charge ID', ['charge_id' => $chargeId]);
            } catch (\Exception $e) {
                \Log::error('Failed to create transaction', [
                    'error' => $e->getMessage(),
                    'transaction_data' => $transactionData,
                ]);
                throw $e;
            }

            // Create entry in lunar_stripe_payment_intents
            $paymentIntentData = [
                'cart_id' => $this->cart->id,
                'order_id' => $orderId,
                'intent_id' => $session->payment_intent,
                'status' => 'succeeded',
                'event_id' => null,
                'processing_at' => now(),
                'processed_at' => now(),
                'created_at' => now(),
                'updated_at' => now(),
            ];

            \Log::info('Creating payment intent record', ['payment_intent_data' => $paymentIntentData]);

            try {
                DB::table('lunar_stripe_payment_intents')->insert($paymentIntentData);
                \Log::info('Payment intent record created successfully');
            } catch (\Exception $e) {
                \Log::error('Failed to create payment intent record', [
                    'error' => $e->getMessage(),
                    'payment_intent_data' => $paymentIntentData,
                ]);
                throw $e;
            }

            // Process commission tracking after order creation
            $this->processCommissionTracking($orderId, $this->cart);

            DB::commit();

            // Clear the cart
            CartSession::forget();

            // Redirect to success page
            return redirect()->route('checkout-success.view')->with('order_id', $orderId);
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Stripe payment processing failed', [
                'session_id' => $sessionId,
                'error' => $e->getMessage(),
                'cart_id' => $this->cart->id ?? null,
            ]);

            session()->flash('error', 'Order processing failed. Please contact support.');
            return;
        }
    }

    /**
     * Handle coupon code submission.
     */
    public function applyCoupon()
    {
        $this->couponError = '';
        $this->appliedCoupon = '';

        $discount = \Lunar\Models\Discount::where('coupon', $this->couponCode)
            ->where('starts_at', '<=', now())
            ->where('ends_at', '>=', now())
            ->first();

        if (! $discount) {
            $this->couponError = 'Invalid or expired coupon code.';
            return;
        }

        // Apply the discount to the cart
        $this->cart->coupon_code = $this->couponCode;
        $this->cart->save();

        // Recalculate the cart to apply discounts
        $this->cart->calculate();

        // Update the cart session
        CartSession::use($this->cart);

        // Force refresh cart from session
        $this->refreshCart();

        $this->appliedCoupon = $this->couponCode;

        // Clear the coupon code input
        $this->couponCode = '';
    }

    /**
     * Return the available countries.
     */
    public function getCountriesProperty(): Collection
    {
        return Country::whereIn('iso3', ['GBR', 'USA'])->get();
    }

    /**
     * Return available shipping options.
     */
    public function getShippingOptionsProperty(): Collection
    {
        return ShippingManifest::getOptions(
            $this->cart
        );
    }

    /**
     * Prepare address data for order address table
     */
    private function prepareAddressData($cartAddress, $orderId, $type)
    {
        // Manually build the data array with ONLY the exact columns that exist
        // This eliminates any possibility of extra fields being included
        $data = [];

        $data['order_id'] = $orderId;
        $data['country_id'] = $cartAddress->country_id;
        $data['title'] = $cartAddress->title ?? null;
        $data['first_name'] = $cartAddress->first_name;
        $data['last_name'] = $cartAddress->last_name;
        $data['company_name'] = $cartAddress->company_name;
        $data['tax_identifier'] = $cartAddress->tax_identifier ?? null;
        $data['line_one'] = $cartAddress->line_one;
        $data['line_two'] = $cartAddress->line_two;
        $data['line_three'] = $cartAddress->line_three;
        $data['city'] = $cartAddress->city;
        $data['state'] = $cartAddress->state;
        $data['postcode'] = $cartAddress->postcode;
        $data['delivery_instructions'] = $cartAddress->delivery_instructions;
        $data['contact_email'] = $cartAddress->contact_email;
        $data['contact_phone'] = $cartAddress->contact_phone;
        $data['type'] = $type;
        $data['shipping_option'] = $cartAddress->shipping_option ?? null;
        $data['meta'] = json_encode($cartAddress->meta ?? []);
        $data['created_at'] = now();
        $data['updated_at'] = now();

        return $data;
    }

    /**
     * Return the address validation rules for a given type.
     */
    protected function getAddressValidation(string $type): array
    {
        return [
            "{$type}.first_name" => 'required',
            "{$type}.last_name" => 'required',
            "{$type}.line_one" => 'required',
            "{$type}.country_id" => 'required',
            "{$type}.city" => 'required',
            "{$type}.postcode" => 'required',
            "{$type}.company_name" => 'nullable',
            "{$type}.line_two" => 'nullable',
            "{$type}.line_three" => 'nullable',
            "{$type}.state" => 'nullable',
            "{$type}.delivery_instructions" => 'nullable',
            "{$type}.contact_email" => 'required|email',
            "{$type}.contact_phone" => 'nullable',
        ];
    }

    private function transformShippingBreakdown($breakdown)
    {
        // Accepts object with 'items' property as a Collection of ShippingBreakdownItem objects
        if (is_object($breakdown) && isset($breakdown->items) && $breakdown->items instanceof \Illuminate\Support\Collection) {
            $items = $breakdown->items;
        } else {
            return [];
        }

        $result = [];
        foreach ($items as $identifier => $item) {
            // $item is a ShippingBreakdownItem object
            if (!$identifier || !isset($item->price) || !isset($item->price->currency)) continue;

            $value = $item->price->value ?? 0;
            $currencyObj = $item->price->currency;
            // Convert Eloquent model to array
            $currency = is_object($currencyObj) && method_exists($currencyObj, 'toArray') ? $currencyObj->toArray() : [];
            $formatted = isset($currency['code']) && $currency['code'] === 'USD'
                ? '$' . number_format($value / 100, 2)
                : (isset($currency['code']) ? number_format($value / 100, 2) . ' ' . $currency['code'] : '');

            $result[$identifier] = [
                'name' => $item->name ?? '',
                'identifier' => $identifier,
                'value' => $value,
                'formatted' => $formatted,
                'currency' => $currency,
            ];
        }
        return $result;
    }

    /**
     * Process commission tracking for affiliates and coupons
     */
    private function processCommissionTracking($orderId, $cart)
    {
        Log::debug('CheckoutNewPage: Starting commission tracking', ['order_id' => $orderId]);

        // Get the order reference for logging
        $order = DB::table('lunar_orders')->where('id', $orderId)->first();
        $orderReference = $order ? $order->reference : $orderId;

        // Check for affiliate coupons first
        $affiliateCouponProcessed = $this->processAffiliateCoupons($orderId, $cart, $orderReference);

        // If no affiliate coupon was processed, check for referrer tracking
        if (!$affiliateCouponProcessed) {
            $this->processReferrerTracking($orderId, $cart, $orderReference);
        }

        Log::debug('CheckoutNewPage: Commission tracking completed', ['order_id' => $orderId]);
    }

    /**
     * Process affiliate coupon commissions
     */
    private function processAffiliateCoupons($orderId, $cart, $orderReference)
    {
        Log::info('CheckoutNewPage: Starting affiliate coupon processing', [
            'order_id' => $orderId,
            'cart_id' => $cart->id,
            'cart_lines_count' => $cart->lines->count(),
            'cart_coupon_code' => $cart->coupon_code,
        ]);

        // Check if cart has a coupon code
        if (empty($cart->coupon_code)) {
            Log::info('CheckoutNewPage: No coupon code found in cart', [
                'order_id' => $orderId,
                'cart_id' => $cart->id,
            ]);
            return false;
        }

        // Check if the coupon is an affiliate coupon
        $couponRow = DB::table('coupons')->where('code', $cart->coupon_code)->first();
        if (!$couponRow || empty($couponRow->affiliate_id)) {
            Log::info('CheckoutNewPage: Coupon is not an affiliate coupon', [
                'order_id' => $orderId,
                'coupon_code' => $cart->coupon_code,
                'has_coupon_row' => !is_null($couponRow),
                'has_affiliate_id' => !empty($couponRow->affiliate_id ?? null),
            ]);
            return false;
        }

        Log::info('CheckoutNewPage: Affiliate coupon found - processing commissions', [
            'order_id' => $orderId,
            'coupon_code' => $cart->coupon_code,
            'affiliate_id' => $couponRow->affiliate_id,
            'coupon_type' => $couponRow->type,
        ]);

        // Use AffiliateCommissionService for proper commission calculation
        $commissionService = app(AffiliateCommissionService::class);

        // Process commission for each cart line
        foreach ($cart->lines as $lineIndex => $cartLine) {
            Log::info('CheckoutNewPage: Processing cart line for affiliate coupon', [
                'order_id' => $orderId,
                'line_index' => $lineIndex,
                'cart_line_id' => $cartLine->id,
                'purchasable_id' => $cartLine->purchasable_id,
                'quantity' => $cartLine->quantity,
            ]);

            // Get product price from cart line
            $productPrice = $cartLine->unitPrice->value / 100; // Convert from cents to dollars

            // Get commission rate for this specific product variant
            $rateInfo = $commissionService->getCommissionRate($couponRow->affiliate_id, $cartLine->purchasable_id);

            // Check if there's a specific product rate for this affiliate and product variant
            $productRate = DB::table('affiliate_product_rates')
                ->where('affiliate_id', $couponRow->affiliate_id)
                ->where('product_variant_id', $cartLine->purchasable_id)
                ->first();

            if ($productRate) {
                // Use product-specific rate
                $commissionRate = $productRate->rate;
                $commissionType = $productRate->rate_type;
                $rateSource = 'product_specific';
            } else {
                // Use affiliate's default rate
                $affiliate = DB::table('affiliates')->where('id', $couponRow->affiliate_id)->first();
                $commissionRate = $affiliate->rate ?? 0;
                $commissionType = $affiliate->rate_type ?? 'percentage';
                $rateSource = 'affiliate_default';
            }

            // Calculate commission amount
            $commissionAmount = $commissionService->calculateCommissionAmount($productPrice, $commissionRate, $commissionType);

            Log::info('CheckoutNewPage: Calculated commission for affiliate coupon', [
                'order_id' => $orderId,
                'affiliate_id' => $couponRow->affiliate_id,
                'product_variant_id' => $cartLine->purchasable_id,
                'product_price' => $productPrice,
                'commission_type' => $commissionType,
                'commission_rate' => $commissionRate,
                'commission_amount' => $commissionAmount,
                'rate_source' => $rateSource,
                'quantity' => $cartLine->quantity,
            ]);

            try {
                // Record affiliate activity using the service
                $activity = $commissionService->recordActivity(
                    affiliateId: $couponRow->affiliate_id,
                    productVariantId: $cartLine->purchasable_id,
                    productPrice: $productPrice,
                    buyerId: $cart->user_id,
                    orderReference: $orderReference . ':affiliate_coupon'
                );

                // Log commission tracking
                DB::table('affiliatecommissionlog')->insert([
                    'affiliate_id' => $couponRow->affiliate_id,
                    'order_id' => $orderId,
                    'type' => 'affiliate_coupon',
                    'created_at' => Carbon::now(),
                    'updated_at' => Carbon::now(),
                ]);

                Log::info('CheckoutNewPage: Successfully recorded affiliate activity for coupon', [
                    'order_id' => $orderId,
                    'affiliate_id' => $couponRow->affiliate_id,
                    'product_variant_id' => $cartLine->purchasable_id,
                    'commission_amount' => $commissionAmount,
                    'order_reference' => $orderReference . ':affiliate_coupon',
                    'activity_id' => $activity->id,
                    'rate_source' => $rateSource,
                ]);
            } catch (\Exception $e) {
                Log::error('CheckoutNewPage: Failed to record affiliate activity for coupon', [
                    'order_id' => $orderId,
                    'affiliate_id' => $couponRow->affiliate_id,
                    'product_variant_id' => $cartLine->purchasable_id,
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString(),
                ]);
            }
        }

        Log::info('CheckoutNewPage: Affiliate coupon processing completed', [
            'order_id' => $orderId,
            'affiliate_coupon_found' => true,
            'coupon_code' => $cart->coupon_code,
            'affiliate_id' => $couponRow->affiliate_id,
            'processed_lines' => $cart->lines->count(),
        ]);

        return true;
    }

    /**
     * Process referrer tracking commissions
     */
    private function processReferrerTracking($orderId, $cart, $orderReference)
    {
        Log::info('CheckoutNewPage: Starting referrer tracking processing', [
            'order_id' => $orderId,
            'cart_id' => $cart->id,
            'cart_lines_count' => $cart->lines->count(),
        ]);

        foreach ($cart->lines as $lineIndex => $cartLine) {
            Log::info('CheckoutNewPage: Processing cart line for referrer tracking', [
                'order_id' => $orderId,
                'line_index' => $lineIndex,
                'cart_line_id' => $cartLine->id,
                'purchasable_id' => $cartLine->purchasable_id,
                'meta_type' => gettype($cartLine->meta),
            ]);

            $meta = $cartLine->meta;

            // Handle different meta formats
            if (is_string($meta)) {
                $metaArr = json_decode($meta, true);
                Log::info('CheckoutNewPage: Parsed meta from JSON string', [
                    'cart_line_id' => $cartLine->id,
                    'meta_string' => $meta,
                    'parsed_meta' => $metaArr,
                ]);
            } elseif (is_array($meta)) {
                $metaArr = $meta;
                Log::info('CheckoutNewPage: Using meta as array', [
                    'cart_line_id' => $cartLine->id,
                    'meta_array' => $metaArr,
                ]);
            } elseif ($meta instanceof \Illuminate\Database\Eloquent\Casts\ArrayObject) {
                $metaArr = $meta->getArrayCopy();
                Log::info('CheckoutNewPage: Converted ArrayObject meta to array', [
                    'cart_line_id' => $cartLine->id,
                    'meta_array' => $metaArr,
                ]);
            } else {
                $metaArr = [];
                Log::info('CheckoutNewPage: No valid meta found, using empty array', [
                    'cart_line_id' => $cartLine->id,
                    'meta_type' => gettype($meta),
                ]);
            }

            $affiliateId = $metaArr['refferer'] ?? null;
            Log::info('CheckoutNewPage: Extracted affiliate ID from meta', [
                'cart_line_id' => $cartLine->id,
                'affiliate_id' => $affiliateId,
                'meta_keys' => array_keys($metaArr),
            ]);

            if (!$affiliateId) {
                Log::info('CheckoutNewPage: No affiliate ID in meta, skipping line', [
                    'cart_line_id' => $cartLine->id,
                    'available_meta_keys' => array_keys($metaArr),
                ]);
                continue;
            }

            // Build affiliate chain (up to 3 tiers)
            $affiliateChain = [];
            $currentAffiliate = Affiliate::find($affiliateId);
            $tier = 1;

            Log::info('CheckoutNewPage: Building affiliate chain', [
                'cart_line_id' => $cartLine->id,
                'starting_affiliate_id' => $affiliateId,
                'found_affiliate' => !is_null($currentAffiliate),
            ]);

            while ($currentAffiliate && $tier <= 3) {
                $affiliateChain[] = $currentAffiliate;
                Log::info('CheckoutNewPage: Added affiliate to chain', [
                    'cart_line_id' => $cartLine->id,
                    'tier' => $tier,
                    'affiliate_id' => $currentAffiliate->id,
                    'parent_id' => $currentAffiliate->parent_id,
                ]);

                if ($currentAffiliate->parent_id) {
                    $currentAffiliate = Affiliate::find($currentAffiliate->parent_id);
                } else {
                    $currentAffiliate = null;
                }
                $tier++;
            }

            Log::info('CheckoutNewPage: Affiliate chain built', [
                'cart_line_id' => $cartLine->id,
                'chain_length' => count($affiliateChain),
                'affiliate_ids' => array_map(fn($a) => $a->id, $affiliateChain),
            ]);

            // Get tiered rates
            $tieredRates = [];
            if (!empty($affiliateChain[0]->tiered_rate_id)) {
                $tieredRateRow = DB::table('tiered_rates')->where('id', $affiliateChain[0]->tiered_rate_id)->first();
                if ($tieredRateRow && !empty($tieredRateRow->tiers)) {
                    $tieredRates = json_decode($tieredRateRow->tiers, true);
                    Log::info('CheckoutNewPage: Found tiered rates', [
                        'cart_line_id' => $cartLine->id,
                        'tiered_rate_id' => $affiliateChain[0]->tiered_rate_id,
                        'tiered_rates' => $tieredRates,
                    ]);
                } else {
                    Log::info('CheckoutNewPage: No tiered rates found', [
                        'cart_line_id' => $cartLine->id,
                        'tiered_rate_id' => $affiliateChain[0]->tiered_rate_id,
                        'has_tiered_rate_row' => !is_null($tieredRateRow),
                        'has_tiers' => !empty($tieredRateRow->tiers ?? null),
                    ]);
                }
            } else {
                Log::info('CheckoutNewPage: No tiered rate ID found', [
                    'cart_line_id' => $cartLine->id,
                    'affiliate_id' => $affiliateChain[0]->id,
                    'tiered_rate_id' => $affiliateChain[0]->tiered_rate_id,
                ]);
            }

            $referralTypes = [
                1 => 'direct',
                2 => 'first_hand',
                3 => 'second_hand',
            ];

            foreach ($affiliateChain as $i => $affiliate) {
                $tierNum = $i + 1;
                $affiliateIdForActivity = $affiliate->id;

                Log::info('CheckoutNewPage: Processing affiliate tier', [
                    'cart_line_id' => $cartLine->id,
                    'tier_num' => $tierNum,
                    'affiliate_id' => $affiliateIdForActivity,
                    'referral_type' => $referralTypes[$tierNum],
                ]);

                // Get commission rate
                $commissionRate = isset($tieredRates[(string)$tierNum]) ? (float)$tieredRates[(string)$tierNum] : null;
                $commissionType = 'percentage';

                Log::info('CheckoutNewPage: Initial commission rate from tiered rates', [
                    'cart_line_id' => $cartLine->id,
                    'tier_num' => $tierNum,
                    'affiliate_id' => $affiliateIdForActivity,
                    'commission_rate' => $commissionRate,
                    'commission_type' => $commissionType,
                ]);

                // For tier 1, check individual product rates if no tiered rate
                if ($tierNum === 1 && $commissionRate === null) {
                    $rateRow = DB::table('affiliate_product_rates')
                        ->where('affiliate_id', $affiliateIdForActivity)
                        ->where('product_variant_id', $cartLine->purchasable_id)
                        ->first();

                    if ($rateRow) {
                        $commissionRate = (float)$rateRow->rate;
                        $commissionType = $rateRow->rate_type;

                        Log::info('CheckoutNewPage: Found individual product rate', [
                            'cart_line_id' => $cartLine->id,
                            'affiliate_id' => $affiliateIdForActivity,
                            'product_variant_id' => $cartLine->purchasable_id,
                            'commission_rate' => $commissionRate,
                            'commission_type' => $commissionType,
                        ]);
                    } else {
                        Log::info('CheckoutNewPage: No individual product rate found', [
                            'cart_line_id' => $cartLine->id,
                            'affiliate_id' => $affiliateIdForActivity,
                            'product_variant_id' => $cartLine->purchasable_id,
                        ]);
                    }
                }

                // Get product price
                $priceRow = DB::table('lunar_prices')
                    ->where('priceable_type', 'product_variant')
                    ->where('priceable_id', $cartLine->purchasable_id)
                    ->where('min_quantity', 1)
                    ->first();

                $productPrice = $priceRow ? $priceRow->price / 100 : 0;

                Log::info('CheckoutNewPage: Product price retrieved', [
                    'cart_line_id' => $cartLine->id,
                    'product_variant_id' => $cartLine->purchasable_id,
                    'product_price' => $productPrice,
                    'has_price_row' => !is_null($priceRow),
                ]);

                // Calculate commission
                if ($commissionRate !== null) {
                    if ($commissionType === 'percentage') {
                        $commissionAmount = $productPrice * ($commissionRate / 100);
                    } elseif ($commissionType === 'flat') {
                        $commissionAmount = $commissionRate;
                    } else {
                        $commissionAmount = 0;
                    }

                    Log::info('CheckoutNewPage: Commission calculated for referrer tracking', [
                        'cart_line_id' => $cartLine->id,
                        'tier_num' => $tierNum,
                        'affiliate_id' => $affiliateIdForActivity,
                        'product_price' => $productPrice,
                        'commission_rate' => $commissionRate,
                        'commission_type' => $commissionType,
                        'commission_amount' => $commissionAmount,
                        'referral_type' => $referralTypes[$tierNum],
                    ]);

                    try {
                        DB::table('affiliate_activity')->insert([
                            'affiliate_id' => $affiliateIdForActivity,
                            'product_variant_id' => $cartLine->purchasable_id,
                            'buyer_id' => $cart->user_id,
                            'product_price' => $productPrice,
                            'commission_rate' => $commissionRate,
                            'commission_type' => $commissionType,
                            'commission_amount' => $commissionAmount,
                            'order_reference' => $orderReference . ':' . $referralTypes[$tierNum],
                            'activity_date' => Carbon::now(),
                            'created_at' => Carbon::now(),
                            'updated_at' => Carbon::now(),
                        ]);

                        DB::table('affiliatecommissionlog')->insert([
                            'affiliate_id' => $affiliateIdForActivity,
                            'order_id' => $orderId,
                            'type' => $referralTypes[$tierNum],
                            'created_at' => Carbon::now(),
                            'updated_at' => Carbon::now(),
                        ]);

                        Log::info('CheckoutNewPage: Successfully inserted affiliate activity for referrer tracking', [
                            'cart_line_id' => $cartLine->id,
                            'affiliate_id' => $affiliateIdForActivity,
                            'product_variant_id' => $cartLine->purchasable_id,
                            'commission_amount' => $commissionAmount,
                            'order_reference' => $orderReference . ':' . $referralTypes[$tierNum],
                            'referral_type' => $referralTypes[$tierNum],
                        ]);
                    } catch (\Exception $e) {
                        Log::error('CheckoutNewPage: Failed to insert affiliate activity for referrer tracking', [
                            'cart_line_id' => $cartLine->id,
                            'affiliate_id' => $affiliateIdForActivity,
                            'product_variant_id' => $cartLine->purchasable_id,
                            'error' => $e->getMessage(),
                        ]);
                    }
                } else {
                    Log::info('CheckoutNewPage: No commission rate found for tier', [
                        'cart_line_id' => $cartLine->id,
                        'affiliate_id' => $affiliateIdForActivity,
                        'tier' => $tierNum,
                        'referral_type' => $referralTypes[$tierNum],
                    ]);
                }
            }
        }

        Log::info('CheckoutNewPage: Referrer tracking processing completed', [
            'order_id' => $orderId,
        ]);
    }

    public function render(): View
    {
        $discounts = DB::table('lunar_discounts')->get();

        return view('livewire.checkout-new-page', [
            'discounts' => $discounts,
            'couponCode' => $this->couponCode,
            'couponError' => $this->couponError,
            'appliedCoupon' => $this->appliedCoupon,
        ])
            ->layout('layouts.checkout');
    }
}
