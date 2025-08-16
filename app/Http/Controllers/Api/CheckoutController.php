<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\CheckoutRequest;
use Illuminate\Http\JsonResponse;
use Lunar\Facades\CartSession;
use Lunar\Facades\Payments;
use Lunar\Facades\ShippingManifest;
use Lunar\Models\Cart;
use Lunar\Models\CartAddress;
use Lunar\Models\Country;

class CheckoutController extends Controller
{
    /**
     * Get available countries for checkout.
     */
    public function countries(): JsonResponse
    {
        $countries = Country::whereIn('iso3', ['GBR', 'USA'])
            ->get()
            ->map(function ($country) {
                return [
                    'id' => $country->id,
                    'name' => $country->name,
                    'iso2' => $country->iso2,
                    'iso3' => $country->iso3,
                ];
            });

        return response()->json([
            'message' => 'Countries retrieved successfully',
            'data' => $countries,
        ]);
    }

    /**
     * Get available shipping options for cart.
     */
    public function shippingOptions(CheckoutRequest $request): JsonResponse
    {
        $cart = $request->getExistingCart();

        if (!$cart) {
            return response()->json([
                'message' => 'Cart not found',
            ], 404);
        }

        if (!$cart->shippingAddress) {
            return response()->json([
                'message' => 'Shipping address required to get shipping options',
            ], 422);
        }

        $options = ShippingManifest::getOptions($cart)->map(function ($option) {
            return [
                'identifier' => $option->getIdentifier(),
                'name' => $option->getName(),
                'description' => $option->getDescription(),
                'price' => $option->getPrice()?->formatted(),
                'price_value' => $option->getPrice()?->value,
            ];
        });

        return response()->json([
            'message' => 'Shipping options retrieved successfully',
            'data' => $options,
        ]);
    }

    /**
     * Set shipping address for cart.
     */
    public function setShippingAddress(CheckoutRequest $request): JsonResponse
    {
        $cart = $request->getExistingCart();

        if (!$cart) {
            return response()->json([
                'message' => 'Cart not found',
            ], 404);
        }

        $address = new CartAddress($request->validated()['shipping_address']);
        $cart->setShippingAddress($address);

        // If shipping is billing, set billing address too
        if ($request->shipping_is_billing) {
            $billingAddress = new CartAddress($request->validated()['shipping_address']);
            $cart->setBillingAddress($billingAddress);
        }

        return response()->json([
            'message' => 'Shipping address set successfully',
            'data' => [
                'cart' => $this->formatCartResponse($cart->fresh()),
                'shipping_address' => $this->formatAddressResponse($cart->shippingAddress),
                'billing_address' => $cart->billingAddress ? $this->formatAddressResponse($cart->billingAddress) : null,
            ],
        ]);
    }

    /**
     * Set billing address for cart.
     */
    public function setBillingAddress(CheckoutRequest $request): JsonResponse
    {
        $cart = $request->getExistingCart();

        if (!$cart) {
            return response()->json([
                'message' => 'Cart not found',
            ], 404);
        }

        $address = new CartAddress($request->validated()['billing_address']);
        $cart->setBillingAddress($address);

        return response()->json([
            'message' => 'Billing address set successfully',
            'data' => [
                'cart' => $this->formatCartResponse($cart->fresh()),
                'billing_address' => $this->formatAddressResponse($cart->billingAddress),
            ],
        ]);
    }

    /**
     * Set shipping option for cart.
     */
    public function setShippingOption(CheckoutRequest $request): JsonResponse
    {
        $cart = $request->getExistingCart();

        if (!$cart) {
            return response()->json([
                'message' => 'Cart not found',
            ], 404);
        }

        if (!$cart->shippingAddress) {
            return response()->json([
                'message' => 'Shipping address required before selecting shipping option',
            ], 422);
        }

        $options = ShippingManifest::getOptions($cart);
        $selectedOption = $options->first(fn ($option) => $option->getIdentifier() === $request->shipping_option);

        if (!$selectedOption) {
            return response()->json([
                'message' => 'Invalid shipping option',
            ], 422);
        }

        CartSession::manager($cart)->setShippingOption($selectedOption);

        return response()->json([
            'message' => 'Shipping option set successfully',
            'data' => [
                'cart' => $this->formatCartResponse($cart->fresh()),
                'selected_option' => [
                    'identifier' => $selectedOption->getIdentifier(),
                    'name' => $selectedOption->getName(),
                    'description' => $selectedOption->getDescription(),
                    'price' => $selectedOption->getPrice()?->formatted(),
                ],
            ],
        ]);
    }

    /**
     * Get checkout summary.
     */
    public function summary(CheckoutRequest $request): JsonResponse
    {
        $cart = $request->getExistingCart();

        if (!$cart) {
            return response()->json([
                'message' => 'Cart not found',
            ], 404);
        }

        $shippingOptions = [];
        if ($cart->shippingAddress) {
            $shippingOptions = ShippingManifest::getOptions($cart)->map(function ($option) {
                return [
                    'identifier' => $option->getIdentifier(),
                    'name' => $option->getName(),
                    'description' => $option->getDescription(),
                    'price' => $option->getPrice()?->formatted(),
                ];
            });
        }

        return response()->json([
            'message' => 'Checkout summary retrieved successfully',
            'data' => [
                'cart' => $this->formatCartResponse($cart),
                'shipping_address' => $cart->shippingAddress ? $this->formatAddressResponse($cart->shippingAddress) : null,
                'billing_address' => $cart->billingAddress ? $this->formatAddressResponse($cart->billingAddress) : null,
                'shipping_options' => $shippingOptions,
                'selected_shipping_option' => $cart->shippingAddress?->shipping_option,
                'checkout_steps' => [
                    'shipping_address' => (bool) $cart->shippingAddress,
                    'shipping_option' => (bool) $cart->shippingAddress?->shipping_option,
                    'billing_address' => (bool) $cart->billingAddress,
                    'ready_for_payment' => $this->isReadyForPayment($cart),
                ],
            ],
        ]);
    }

    /**
     * Process checkout and create order.
     */
    public function processCheckout(CheckoutRequest $request): JsonResponse
    {
        $cart = $request->getExistingCart();

        if (!$cart) {
            return response()->json([
                'message' => 'Cart not found',
            ], 404);
        }

        if (!$this->isReadyForPayment($cart)) {
            return response()->json([
                'message' => 'Cart is not ready for checkout',
                'errors' => $this->getCheckoutErrors($cart),
            ], 422);
        }

        try {
            // Process payment with cash-in-hand
            $payment = Payments::driver('cash-in-hand')
                ->cart($cart)
                ->authorize();

            if ($payment->success) {
                $order = $cart->completedOrder;
                
                return response()->json([
                    'message' => 'Order created successfully',
                    'data' => [
                        'order' => $this->formatOrderResponse($order),
                        'payment_status' => 'success',
                        'payment_method' => 'cash-in-hand',
                    ],
                ]);
            } else {
                return response()->json([
                    'message' => 'Payment failed',
                    'error' => 'Unable to process payment',
                ], 422);
            }
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Checkout failed',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Check if cart is ready for payment.
     */
    private function isReadyForPayment(Cart $cart): bool
    {
        return $cart->shippingAddress && 
               $cart->billingAddress && 
               $cart->shippingAddress->shipping_option &&
               $cart->lines->count() > 0;
    }

    /**
     * Get checkout validation errors.
     */
    private function getCheckoutErrors(Cart $cart): array
    {
        $errors = [];

        if (!$cart->shippingAddress) {
            $errors[] = 'Shipping address is required';
        }

        if (!$cart->billingAddress) {
            $errors[] = 'Billing address is required';
        }

        if (!$cart->shippingAddress?->shipping_option) {
            $errors[] = 'Shipping option is required';
        }

        if ($cart->lines->count() === 0) {
            $errors[] = 'Cart is empty';
        }

        return $errors;
    }

    /**
     * Format cart response.
     */
    private function formatCartResponse($cart): array
    {
        if (!$cart) {
            return [];
        }

        return [
            'id' => $cart->id,
            'cart_id' => $cart->session_id,
            'user_id' => $cart->customer?->users()->first()?->id,
            'customer_id' => $cart->customer_id,
            'total' => $cart->total?->formatted(),
            'sub_total' => $cart->subTotal?->formatted(),
            'tax_total' => $cart->taxTotal?->formatted(),
            'discount_total' => $cart->discountTotal?->formatted(),
            'shipping_total' => $cart->shippingTotal?->formatted(),
            'lines_count' => $cart->lines->count(),
            'total_quantity' => $cart->lines->sum('quantity'),
            'lines' => $cart->lines->map(function ($line) {
                return [
                    'id' => $line->id,
                    'quantity' => $line->quantity,
                    'unit_price' => $line->unitPrice?->formatted(),
                    'sub_total' => $line->subTotal?->formatted(),
                    'total' => $line->total?->formatted(),
                    'product' => [
                        'id' => $line->purchasable->product->id,
                        'name' => $line->purchasable->product->translateAttribute('name'),
                        'slug' => $line->purchasable->product->defaultUrl?->slug,
                        'thumbnail' => $line->purchasable->product->thumbnail?->getUrl(),
                    ],
                    'variant' => [
                        'id' => $line->purchasable->id,
                        'sku' => $line->purchasable->sku,
                        'stock' => $line->purchasable->stock,
                    ],
                ];
            }),
            'created_at' => $cart->created_at,
            'updated_at' => $cart->updated_at,
        ];
    }

    /**
     * Format address response.
     */
    private function formatAddressResponse($address): array
    {
        if (!$address) {
            return [];
        }

        return [
            'id' => $address->id,
            'first_name' => $address->first_name,
            'last_name' => $address->last_name,
            'company_name' => $address->company_name,
            'line_one' => $address->line_one,
            'line_two' => $address->line_two,
            'line_three' => $address->line_three,
            'city' => $address->city,
            'state' => $address->state,
            'postcode' => $address->postcode,
            'country_id' => $address->country_id,
            'country' => $address->country ? [
                'id' => $address->country->id,
                'name' => $address->country->name,
                'iso2' => $address->country->iso2,
                'iso3' => $address->country->iso3,
            ] : null,
            'delivery_instructions' => $address->delivery_instructions,
            'contact_email' => $address->contact_email,
            'contact_phone' => $address->contact_phone,
            'type' => $address->type,
        ];
    }

    /**
     * Format order response.
     */
    private function formatOrderResponse($order): array
    {
        if (!$order) {
            return [];
        }

        return [
            'id' => $order->id,
            'reference' => $order->reference,
            'status' => $order->status,
            'total' => $order->total?->formatted(),
            'sub_total' => $order->subTotal?->formatted(),
            'tax_total' => $order->taxTotal?->formatted(),
            'discount_total' => $order->discountTotal?->formatted(),
            'shipping_total' => $order->shippingTotal?->formatted(),
            'customer' => [
                'id' => $order->customer_id,
                'name' => $order->customer?->fullName,
                'email' => $order->customer?->users()->first()?->email,
            ],
            'shipping_address' => $order->shippingAddress ? $this->formatAddressResponse($order->shippingAddress) : null,
            'billing_address' => $order->billingAddress ? $this->formatAddressResponse($order->billingAddress) : null,
            'lines' => $order->lines->map(function ($line) {
                return [
                    'id' => $line->id,
                    'quantity' => $line->quantity,
                    'unit_price' => $line->unitPrice?->formatted(),
                    'total' => $line->total?->formatted(),
                    'product_name' => $line->description,
                    'product_sku' => $line->identifier,
                ];
            }),
            'created_at' => $order->created_at,
            'updated_at' => $order->updated_at,
        ];
    }
}