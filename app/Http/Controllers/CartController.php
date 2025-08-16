<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Lunar\Facades\CartSession;
use Lunar\Models\ProductVariant;

class CartController extends Controller
{
    /**
     * Display the cart details.
     */
    public function __invoke(): JsonResponse
    {
        $cart = CartSession::current();

        return response()->json([
            'cart' => $cart,
        ]);
    }

    /**
     * Add a product to the cart.
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'purchasable_id' => 'required|integer|exists:product_variants,id',
            'quantity' => 'required|integer|min:1|max:10000',
            // Add more fields if needed (e.g., meta, options)
        ]);

        $variant = ProductVariant::findOrFail($validated['purchasable_id']);

        if ($variant->stock < $validated['quantity']) {
            return response()->json([
                'error' => 'The quantity exceeds the available stock.'
            ], 422);
        }

        $cartLine = CartSession::manager()->add($variant, $validated['quantity']);

        return response()->json([
            'message' => 'Product added to cart.',
            'cart_line' => $cartLine,
            'cart' => CartSession::current(),
        ]);
    }
}
