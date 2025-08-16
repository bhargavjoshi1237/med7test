<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Stripe\Stripe;
use Stripe\Checkout\Session;

class StripeController extends Controller
{
    public function checkout()
{
    Stripe::setApiKey(env('STRIPE_SECRET'));

    // Get the current cart from session
    $cart = \Lunar\Facades\CartSession::current();

    // Calculate total amount in cents
    $amount = $cart ? $cart->total->value : 1000; // fallback $10 if cart missing

    // Build description from cart lines
    $description = $cart && $cart->lines->count()
        ? implode(', ', $cart->lines->map(fn($line) => $line->purchasable->getDescription())->toArray())
        : 'Simple Payment';

    $session = Session::create([
        'payment_method_types' => ['card'],
        'line_items' => [[
            'price_data' => [
                'currency' => 'usd',
                'product_data' => [
                    'name' => $description,
                ],
                'unit_amount' => $amount, // amount in cents
            ],
            'quantity' => 1,
        ]],
        'mode' => 'payment',
        // The key change is here - let Stripe handle the session ID replacement
        'success_url' => route('payment.success', [], true) . '?session_id={CHECKOUT_SESSION_ID}',
        'cancel_url' => route('payment.cancel', [], true),
    ]);

    return redirect($session->url);
}

public function success(Request $request)
{
    // Get the session ID from the query parameter
    $sessionId = $request->query('session_id');
    $paymentIntentId = null;
    
    if ($sessionId) {
        try {
            Stripe::setApiKey(env('STRIPE_SECRET'));
            $session = \Stripe\Checkout\Session::retrieve($sessionId);
            $paymentIntentId = $session->payment_intent;
            
            // You can store this information in the database or session
            $request->session()->put('payment_intent_id', $paymentIntentId);
            
        } catch (\Exception $e) {
            // Log the error
            \Log::error('Error retrieving Stripe session: ' . $e->getMessage());
        }
    }
    
    return view('payment.success', ['payment_intent_id' => $paymentIntentId]);
}

/**
 * Process a full or partial refund
 * 
 * @param string $paymentIntentId
 * @param \Illuminate\Http\Request $request
 * @return \Illuminate\Http\JsonResponse
 */
public function refund($paymentIntentId, Request $request)
{
    Stripe::setApiKey(env('STRIPE_SECRET'));
    
    try {
        // Validate the refund amount if provided
        $validatedData = $request->validate([
            'amount' => 'nullable|numeric|min:0.01',
        ]);
        
        $refundParams = [
            'payment_intent' => $paymentIntentId,
        ];
        
        // If amount is provided, convert to cents for Stripe
        if ($request->has('amount') && $request->amount > 0) {
            $refundParams['amount'] = (int) ($request->amount * 100); // Convert dollars to cents
        }
        
        $refund = \Stripe\Refund::create($refundParams);
        
        // Update payment status in your database
        $payment = \App\Models\Payment::where('payment_intent_id', $paymentIntentId)->first();
        if ($payment) {
            // Check if this is a full or partial refund
            if (!$request->has('amount')) {
                $payment->status = 'refunded';
            } else {
                $totalRefunded = $payment->refunded_amount + $request->amount;
                $payment->refunded_amount = $totalRefunded;
                
                if ($totalRefunded >= $payment->amount) {
                    $payment->status = 'refunded';
                } else {
                    $payment->status = 'partially_refunded';
                }
            }
            $payment->save();
        }
        
        return response()->json([
            'success' => true, 
            'refund' => $refund,
            'message' => $request->has('amount') ? 'Partial refund processed' : 'Full refund processed'
        ]);
    } catch (\Exception $e) {
        return response()->json(['success' => false, 'error' => $e->getMessage()], 400);
    }
}
public function refundHistory($paymentIntentId)
{
    Stripe::setApiKey(env('STRIPE_SECRET'));
    
    try {
        $refunds = \Stripe\Refund::all([
            'payment_intent' => $paymentIntentId,
        ]);
        
        $payment = \App\Models\Payment::where('payment_intent_id', $paymentIntentId)->first();
        
        return view('payment.refund-history', [
            'refunds' => $refunds->data,
            'payment' => $payment,
        ]);
    } catch (\Exception $e) {
        return back()->with('error', $e->getMessage());
    }
}
    public function cancel()
    {
        return view('payment.cancel');
    }
}