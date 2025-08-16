<?php

namespace App\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Database\Eloquent\Builder;
use Lunar\Models\Cart;

class CartRequest extends FormRequest
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

        // Add specific rules based on the route action
        if ($this->routeIs('api.cart.add')) {
            $rules = array_merge($rules, [
                'purchasable_id' => 'required|integer|exists:lunar_product_variants,id',
                'quantity' => 'required|integer|min:1|max:10000',
                'meta' => 'nullable|array',
            ]);
        }

        if ($this->routeIs('api.cart.update')) {
            $rules = array_merge($rules, [
                'lines' => 'required|array',
                'lines.*.id' => 'required|integer|exists:lunar_cart_lines,id',
                'lines.*.quantity' => 'required|integer|min:0|max:10000',
            ]);
        }

        if ($this->routeIs('api.cart.remove')) {
            $rules = array_merge($rules, [
                'line_id' => 'required|integer|exists:lunar_cart_lines,id',
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
            'purchasable_id.exists' => 'The specified product variant does not exist.',
            'quantity.max' => 'Quantity cannot exceed 10,000.',
            'lines.*.quantity.min' => 'Quantity must be at least 0 (use 0 to remove item).',
        ];
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
            \Log::info('Customer ID: ' . $customer->id);
            
            if (!$cart) {
                // Check if there's an existing guest cart to update
                if ($cartId) {
                    $existingCart = Cart::where('session_id', $cartId)->first();
                    if ($existingCart && !$existingCart->customer_id) {
                        // Update existing guest cart with customer_id
                        $existingCart->update(['customer_id' => $customer->id]);
                        return $existingCart;
            }
                }
            
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
}