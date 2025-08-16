<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Lunar\Models\Order;

class OrderController extends Controller
{
    /**
     * Get user's orders.
     */
    public function index(Request $request): JsonResponse
    {
        $request->validate([
            'user_id' => 'required|integer|exists:users,id',
            'status' => 'nullable|string',
            'per_page' => 'nullable|integer|min:1|max:100',
        ]);

        $user = \App\Models\User::find($request->user_id);
        $customer = $user->customers()->first();

        if (!$customer) {
            return response()->json([
                'message' => 'No orders found',
                'data' => [],
            ]);
        }

        $query = Order::where('customer_id', $customer->id);

        if ($request->status) {
            $query->where('status', $request->status);
        }

        $orders = $query->orderBy('created_at', 'desc')
            ->paginate($request->per_page ?? 15);

        return response()->json([
            'message' => 'Orders retrieved successfully',
            'data' => [
                'orders' => $orders->items(),
                'pagination' => [
                    'current_page' => $orders->currentPage(),
                    'last_page' => $orders->lastPage(),
                    'per_page' => $orders->perPage(),
                    'total' => $orders->total(),
                ],
            ],
        ]);
    }

    /**
     * Get specific order details.
     */
    public function show(Request $request, int $orderId): JsonResponse
    {
        $request->validate([
            'user_id' => 'required|integer|exists:users,id',
        ]);

        $user = \App\Models\User::find($request->user_id);
        $customer = $user->customers()->first();

        if (!$customer) {
            return response()->json([
                'message' => 'Order not found',
            ], 404);
        }

        $order = Order::where('id', $orderId)
            ->where('customer_id', $customer->id)
            ->first();

        if (!$order) {
            return response()->json([
                'message' => 'Order not found',
            ], 404);
        }

        return response()->json([
            'message' => 'Order retrieved successfully',
            'data' => $this->formatOrderResponse($order),
        ]);
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
            'transactions' => $order->transactions->map(function ($transaction) {
                return [
                    'id' => $transaction->id,
                    'type' => $transaction->type,
                    'driver' => $transaction->driver,
                    'amount' => $transaction->amount?->formatted(),
                    'reference' => $transaction->reference,
                    'status' => $transaction->status,
                    'notes' => $transaction->notes,
                    'created_at' => $transaction->created_at,
                ];
            }),
            'created_at' => $order->created_at,
            'updated_at' => $order->updated_at,
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
}