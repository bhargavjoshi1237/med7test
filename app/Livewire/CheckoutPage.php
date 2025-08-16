<?php

namespace App\Livewire;

use Illuminate\Support\Collection;
use Illuminate\View\View;
use Livewire\Component;
use Lunar\Facades\CartSession;
use Lunar\Facades\Payments;
use Lunar\Facades\ShippingManifest;
use Lunar\Models\Cart;
use Lunar\Models\CartAddress;
use Lunar\Models\Country;
use Illuminate\Support\Facades\DB;
use Stripe\Stripe;
use Stripe\Checkout\Session as StripeSession;

class CheckoutPage extends Component
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
     * The payment type we want to use.
     */
    public string $paymentType = 'cash-in-hand';

    /**
     * {@inheritDoc}
     */
    protected $listeners = [
        'cartUpdated' => 'refreshCart',
        'selectedShippingOption' => 'refreshCart',
    ];

    public $payment_intent = null;

    public $payment_intent_client_secret = null;

    protected $queryString = [
        'payment_intent',
        'payment_intent_client_secret',
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

        // Handle Stripe callback
        if (request()->query('stripe_success') && request()->query('session_id')) {
            $this->handleStripeSuccess(request()->query('session_id'));
            return;
        }

        // Comment out old payment code
        /*
        if ($this->payment_intent) {
            $payment = Payments::driver($this->paymentType)->cart($this->cart)->withData([
                'payment_intent_client_secret' => $this->payment_intent_client_secret,
                'payment_intent' => $this->payment_intent,
            ])->authorize();
            if ($payment->success) {
                \Log::info('Order placed successfully', [
                    'cart_id' => $this->cart->id,
                    'user_id' => $this->cart->user_id ?? null,
                    'payment_type' => $this->paymentType,
                ]);
                Transaction::create([
                    'success' => 1,
                    'driver' => $this->paymentType,
                    'amount' => $this->cart->total->value,
                    'reference' => $payment->reference ?? '',
                    'status' => $payment->status ?? 'captured',
                    'notes' => $payment->notes ?? null,
                    'card_type' => $payment->card_type ?? '',
                    'last_four' => $payment->last_four ?? null,
                    'meta' => isset($payment->meta) ? json_encode($payment->meta) : null,
                    'created_at' => now(),
                    'updated_at' => now(),
                    'parent_transaction_id' => $payment->parent_transaction_id ?? null,
                    'captured_at' => now(),
                    'type' => 'capture',
                ]);
                redirect()->route('checkout-success.view');
                return;
            }
        }
        */

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
                $this->chosenShipping = $this->shippingOptions->first()?->getIdentifier();

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
        }

        $this->determineCheckoutStep();
    }

    /**
     * Save the selected shipping option.
     */
    public function saveShippingOption(): void
    {
        $option = $this->shippingOptions->first(fn ($option) => $option->getIdentifier() == $this->chosenShipping);

        CartSession::setShippingOption($option);

        $this->refreshCart();

        $this->determineCheckoutStep();
    }

    public function initiateStripePayment()
    {
        // Redirect to StripeController's checkout route instead of handling Stripe here
        return redirect()->route('payment');
    }

    public function handleStripeSuccess($sessionId)
    {
        Stripe::setApiKey(env('STRIPE_SECRET'));
        $session = StripeSession::retrieve($sessionId);

        // Create order in lunar_orders
        $order = \Lunar\Models\Order::create([
            'user_id' => $this->cart->user_id ?? null,
            'channel_id' => 1,
            'status' => 'paid',
            'reference' => $session->id,
            'sub_total' => $this->cart->sub_total->value,
            'discount_total' => $this->cart->discount_total->value,
            'shipping_total' => $this->cart->shipping_total->value,
            'tax_breakdown' => json_encode($this->cart->tax_breakdown),
            'tax_total' => $this->cart->tax_total->value,
            'total' => $this->cart->total->value,
            'notes' => null,
            'currency_code' => $this->cart->currency->code,
            'compare_currency_code' => null,
            'exchange_rate' => 1,
            'placed_at' => now(),
            'meta' => null,
            'customer_id' => $this->cart->customer_id ?? null,
            'new_customer' => 0,
            'discount_breakdown' => json_encode($this->cart->discount_breakdown),
            'shipping_breakdown' => json_encode($this->cart->shipping_breakdown),
            'cart_id' => $this->cart->id,
            'fingerprint' => null,
        ]);
        $orderId = $order->id;

        // Create transaction in lunar_transactions
        DB::table('lunar_transactions')->insert([
            'order_id' => $orderId,
            'success' => 1,
            'driver' => 'stripe',
            'amount' => $this->cart->total->value,
            'reference' => $session->payment_intent,
            'status' => 'captured',
            'notes' => null,
            'card_type' => '',
            'last_four' => null,
            'meta' => null,
            'created_at' => now(),
            'updated_at' => now(),
            'parent_transaction_id' => null,
            'captured_at' => now(),
            'type' => 'capture',
        ]);

        // Create entry in lunar_stripe_payment_intents
        DB::table('lunar_stripe_payment_intents')->insert([
            'cart_id' => $this->cart->id,
            'order_id' => $orderId,
            'intent_id' => $session->payment_intent,
            'status' => 'succeeded',
            'event_id' => null,
            'processing_at' => now(),
            'processed_at' => now(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Redirect to order success page
        redirect()->route('checkout-success.view');
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

        $this->cart->coupon_code = $this->couponCode;
        $this->cart->save();
        $this->cart->calculate();

        // Force refresh cart from session
        $this->refreshCart();

        $this->appliedCoupon = $this->couponCode;
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

    public function render(): View
    {
        $discounts = DB::table('lunar_discounts')->get();

        return view('livewire.checkout-page', [
            'discounts' => $discounts,
            'couponCode' => $this->couponCode,
            'couponError' => $this->couponError,
            'appliedCoupon' => $this->appliedCoupon,
        ])
            ->layout('layouts.checkout');
    }
}
           
