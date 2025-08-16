<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\CartRequest;
use Illuminate\Http\JsonResponse;
use Lunar\Facades\CartSession;
use Lunar\Models\ProductVariant;

class CartController extends Controller
{
    /**
     * Get cart details.
     */
    public function show(CartRequest $request): JsonResponse
    {
        // If user_id is provided, create cart if it doesn't exist
        if ($request->input('user_id')) {
            $cart = $request->getOrCreateCart();
        } else {
            $cart = $request->getExistingCart();
        }

        if (!$cart) {
            return response()->json([
                'message' => 'Cart not found',
                'data' => null,
            ], 404);
        }

        return response()->json([
            'message' => 'Cart retrieved successfully',
            'data' => $this->formatCartResponse($cart),
        ]);
    }

    /**
     * Create a new cart or get existing one.
     */
    public function create(CartRequest $request): JsonResponse
    {
        $cart = $request->getOrCreateCart();

        return response()->json([
            'message' => 'Cart created successfully',
            'data' => $this->formatCartResponse($cart),
        ], 201);
    }

    /**
     * Add item to cart.
     */
    public function add(CartRequest $request): JsonResponse
    {
        $cart = $request->getOrCreateCart();
        $variant = ProductVariant::findOrFail($request->purchasable_id);

        // Check stock availability
        if ($variant->stock < $request->quantity) {
            return response()->json([
                'message' => 'Insufficient stock available',
                'error' => 'The quantity exceeds the available stock.',
                'available_stock' => $variant->stock,
            ], 422);
        }

        // Add to cart
        $cartLine = CartSession::manager($cart)->add(
            $variant,
            $request->quantity,
            $request->meta ?? []
        );

        return response()->json([
            'message' => 'Item added to cart successfully',
            'data' => [
                'cart' => $this->formatCartResponse($cart->fresh()),
                'added_line' => [
                    'id' => $cartLine->id,
                    'quantity' => $cartLine->quantity,
                    'product' => [
                        'id' => $variant->product->id,
                        'name' => $variant->product->translateAttribute('name'),
                        'variant_id' => $variant->id,
                        'sku' => $variant->sku,
                    ],
                ],
            ],
        ]);
    }

    /**
     * Update cart lines.
     */
    public function update(CartRequest $request): JsonResponse
    {
        $cart = $request->getExistingCart();

        if (!$cart) {
            return response()->json([
                'message' => 'Cart not found',
            ], 404);
        }

        $updatedLines = [];
        $removedLines = [];

        foreach ($request->lines as $lineData) {
            $line = $cart->lines()->find($lineData['id']);
            
            if (!$line) {
                continue;
            }

            if ($lineData['quantity'] == 0) {
                // Remove line if quantity is 0
                $removedLines[] = $line->id;
                CartSession::manager($cart)->remove($line->id);
            } else {
                // Update quantity
                CartSession::manager($cart)->updateLine($line->id, $lineData['quantity']);
                $updatedLines[] = [
                    'id' => $line->id,
                    'quantity' => $lineData['quantity'],
                ];
            }
        }

        return response()->json([
            'message' => 'Cart updated successfully',
            'data' => [
                'cart' => $this->formatCartResponse($cart->fresh()),
                'updated_lines' => $updatedLines,
                'removed_lines' => $removedLines,
            ],
        ]);
    }

    /**
     * Remove item from cart.
     */
    public function remove(CartRequest $request): JsonResponse
    {
        $cart = $request->getExistingCart();

        if (!$cart) {
            return response()->json([
                'message' => 'Cart not found',
            ], 404);
        }

        $line = $cart->lines()->find($request->line_id);

        if (!$line) {
            return response()->json([
                'message' => 'Cart line not found',
            ], 404);
        }

        CartSession::manager($cart)->remove($request->line_id);

        return response()->json([
            'message' => 'Item removed from cart successfully',
            'data' => [
                'cart' => $this->formatCartResponse($cart->fresh()),
                'removed_line_id' => $request->line_id,
            ],
        ]);
    }

    /**
     * Clear entire cart.
     */
    public function clear(CartRequest $request): JsonResponse
    {
        $cart = $request->getExistingCart();

        if (!$cart) {
            return response()->json([
                'message' => 'Cart not found',
            ], 404);
        }

        CartSession::manager($cart)->clear();

        return response()->json([
            'message' => 'Cart cleared successfully',
            'data' => $this->formatCartResponse($cart->fresh()),
        ]);
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
                        'options' => $line->purchasable->values->map(function ($value) {
                            return [
                                'option' => $value->option->translate('name'),
                                'value' => $value->translate('name'),
                            ];
                        }),
                    ],
                ];
            }),
            'created_at' => $cart->created_at,
            'updated_at' => $cart->updated_at,
        ];
    }
}