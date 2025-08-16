<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Lunar\Facades\CartSession;

class HandleCartSession
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next)
    {
        // Only handle cart session for authenticated users
        if (Auth::check()) {
            $user = Auth::user();
            $customer = $user->customers()->first();
            
            if ($customer) {
                // Set the cart session to use the customer's cart
                $cart = $customer->carts()->first();
                if ($cart) {
                    CartSession::use($cart);
                }
            }
        }

        return $next($request);
    }
}