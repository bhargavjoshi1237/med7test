<?php

namespace App\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;
use Lunar\Models\Cart;

class CheckoutRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        $rules = [
            'user_id' => 'nullable|integer|exists:users,id',
            'cart_id' => 'nullable|string|uuid',
        ];

        // Shipping address rules
        if ($this->routeIs('api.checkout.shipping-address')) {
            $rules = array_merge($rules, [
                'shipping_address.first_name' => 'required|string|max:255',
                'shipping_address.last_name' => 'required|string|max:255',
                'shipping_address.company_name' => 'nullable|string|max:255',
                'shipping_address.line_one' => 'required|string|max:255',
                'shipping_address.line_two' => 'nullable|string|max:255',
                'shipping_address.line_three' => 'nullable|string|max:255',
                'shipping_address.city' => 'required|string|max:255',
                'shipping_address.state' => 'nullable|string|max:255',
                'shipping_address.postcode' => 'required|string|max:20',
                'shipping_address.country_id' => 'required|integer|exists:countries,id',
                'shipping_address.delivery_instructions' => 'nullable|string|max:500',
                'shipping_address.contact_email' => 'required|email|max:255',
                'shipping_address.contact_phone' => 'nullable|string|max:20',
                'shipping_is_billing' => 'boolean',
            ]);
        }

        // Billing address rules
        if ($this->routeIs('api.checkout.billing-address')) {
            $rules = array_merge($rules, [
                'billing_address.first_name' => 'required|string|max:255',
                'billing_address.last_name' => 'required|string|max:255',
                'billing_address.company_name' => 'nullable|string|max:255',
                'billing_address.line_one' => 'required|string|max:255',
                'billing_address.line_two' => 'nullable|string|max:255',
                'billing_address.line_three' => 'nullable|string|max:255',
                'billing_address.city' => 'required|string|max:255',
                'billing_address.state' => 'nullable|string|max:255',
                'billing_address.postcode' => 'required|string|max:20',
                'billing_address.country_id' => 'required|integer|exists:countries,id',
                'billing_address.delivery_instructions' => 'nullable|string|max:500',
                'billing_address.contact_email' => 'required|email|max:255',
                'billing_address.contact_phone' => 'nullable|string|max:20',
            ]);
        }

        // Shipping option rules
        if ($this->routeIs('api.checkout.shipping-option')) {
            $rules = array_merge($rules, [
                'shipping_option' => 'required|string',
            ]);
        }

        return $rules;
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'user_id.exists' => 'The specified user does not exist.',
            'shipping_address.first_name.required' => 'First name is required for shipping address.',
            'shipping_address.last_name.required' => 'Last name is required for shipping address.',
            'shipping_address.line_one.required' => 'Address line 1 is required for shipping address.',
            'shipping_address.city.required' => 'City is required for shipping address.',
            'shipping_address.postcode.required' => 'Postcode is required for shipping address.',
            'shipping_address.country_id.required' => 'Country is required for shipping address.',
            'shipping_address.country_id.exists' => 'The selected country is invalid.',
            'shipping_address.contact_email.required' => 'Contact email is required for shipping address.',
            'shipping_address.contact_email.email' => 'Contact email must be a valid email address.',
            'billing_address.first_name.required' => 'First name is required for billing address.',
            'billing_address.last_name.required' => 'Last name is required for billing address.',
            'billing_address.line_one.required' => 'Address line 1 is required for billing address.',
            'billing_address.city.required' => 'City is required for billing address.',
            'billing_address.postcode.required' => 'Postcode is required for billing address.',
            'billing_address.country_id.required' => 'Country is required for billing address.',
            'billing_address.country_id.exists' => 'The selected country is invalid.',
            'billing_address.contact_email.required' => 'Contact email is required for billing address.',
            'billing_address.contact_email.email' => 'Contact email must be a valid email address.',
            'shipping_option.required' => 'Shipping option is required.',
        ];
    }

    /**
     * Get existing cart or return null.
     */
    public function getExistingCart(): ?Cart
    {
        $userId = $this->input('user_id');
        $cartId = $this->input('cart_id');

        if ($userId) {
            $user = \App\Models\User::find($userId);
            if (!$user) {
                return null;
            }
            $customer = $user->customers()->first();
            return $customer ? Cart::where('customer_id', $customer->id)->first() : null;
        }

        if ($cartId) {
            return Cart::where('session_id', $cartId)->first();
        }

        return null;
    }

    /**
     * Get or create cart based on request parameters.
     */
    public function getOrCreateCart(): Cart
    {
        $userId = $this->input('user_id');
        $cartId = $this->input('cart_id');

        if ($userId) {
            // User cart - find or create by customer_id
            $user = \App\Models\User::find($userId);
            if (!$user) {
                throw new \Exception('User not found');
            }
            
            $customer = $user->customers()->first();
            
            if (!$customer) {
                // Create customer if doesn't exist
                $customer = \Lunar\Models\Customer::create([
                    'first_name' => $user->name ?: 'Customer',
                    'last_name' => '',
                ]);
                $customer->users()->attach($user);
            }

            // Find existing cart or create new one
            $cart = Cart::where('customer_id', $customer->id)->first();
            
            if (!$cart) {
                $cart = Cart::create([
                    'customer_id' => $customer->id,
                    'session_id' => \Illuminate\Support\Str::uuid(),
                ]);
            }
            
            return $cart;
        }

        if ($cartId) {
            // Guest cart - find by session_id
            return Cart::firstOrCreate([
                'session_id' => $cartId,
            ]);
        }

        // Create new guest cart with generated UUID
        return Cart::create([
            'session_id' => \Illuminate\Support\Str::uuid(),
        ]);
    }
}