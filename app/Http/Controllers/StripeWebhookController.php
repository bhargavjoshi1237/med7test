<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Stripe\Stripe;
use Stripe\Webhook;
use Stripe\Exception\SignatureVerificationException;

class StripeWebhookController extends Controller
{
    public function handleWebhook(Request $request)
{
    Stripe::setApiKey(env('STRIPE_SECRET'));
    
    $payload = $request->getContent();
    $sig_header = $request->server('HTTP_STRIPE_SIGNATURE');
    $webhook_secret = env('STRIPE_WEBHOOK_SECRET');
    
    try {
        $event = \Stripe\Webhook::constructEvent(
            $payload, $sig_header, $webhook_secret
        );
    } catch (\Stripe\Exception\SignatureVerificationException $e) {
        \Log::error('Webhook signature verification failed', ['error' => $e->getMessage()]);
        return response()->json(['error' => $e->getMessage()], 400);
    }
    
    // Handle the event
    switch ($event->type) {
        case 'checkout.session.completed':
            $this->handleCheckoutSessionCompleted($event->data->object);
            break;
            
        case 'payment_intent.succeeded':
            $this->handlePaymentIntentSucceeded($event->data->object);
            break;
            
        case 'payment_intent.payment_failed':
            $this->handlePaymentIntentFailed($event->data->object);
            break;
            
        case 'charge.succeeded':
            $this->handleChargeSucceeded($event->data->object);
            break;
        
        default:
            \Log::info('Unhandled webhook event type', ['type' => $event->type]);
    }
    
    return response()->json(['status' => 'success']);
}

    /**
     * Handle checkout session completed event
     */
    private function handleCheckoutSessionCompleted($session)
    {
        \Log::info('Checkout session completed', [
            'session_id' => $session->id,
            'payment_intent' => $session->payment_intent,
            'amount_total' => $session->amount_total,
        ]);

        // Update payment intent status if exists
        if ($session->payment_intent) {
            \DB::table('lunar_stripe_payment_intents')
                ->where('intent_id', $session->payment_intent)
                ->update([
                    'status' => 'succeeded',
                    'processed_at' => now(),
                    'updated_at' => now(),
                ]);
        }
    }

    /**
     * Handle payment intent succeeded event
     */
    private function handlePaymentIntentSucceeded($paymentIntent)
    {
        \Log::info('Payment intent succeeded', [
            'payment_intent_id' => $paymentIntent->id,
            'amount' => $paymentIntent->amount,
        ]);

        // Update payment intent and transaction status
        \DB::table('lunar_stripe_payment_intents')
            ->where('intent_id', $paymentIntent->id)
            ->update([
                'status' => 'succeeded',
                'processed_at' => now(),
                'updated_at' => now(),
            ]);

        // Update transaction status
        \DB::table('lunar_transactions')
            ->where('reference', $paymentIntent->id)
            ->update([
                'status' => 'captured',
                'captured_at' => now(),
                'updated_at' => now(),
            ]);
    }

    /**
     * Handle payment intent failed event
     */
    private function handlePaymentIntentFailed($paymentIntent)
    {
        \Log::warning('Payment intent failed', [
            'payment_intent_id' => $paymentIntent->id,
            'last_payment_error' => $paymentIntent->last_payment_error,
        ]);

        // Update payment intent status
        \DB::table('lunar_stripe_payment_intents')
            ->where('intent_id', $paymentIntent->id)
            ->update([
                'status' => 'failed',
                'updated_at' => now(),
            ]);

        // Update transaction status
        \DB::table('lunar_transactions')
            ->where('reference', $paymentIntent->id)
            ->update([
                'status' => 'failed',
                'success' => 0,
                'notes' => 'Payment failed: ' . ($paymentIntent->last_payment_error->message ?? 'Unknown error'),
                'updated_at' => now(),
            ]);
    }

    /**
     * Handle charge succeeded event - update transaction with proper charge ID
     */
    private function handleChargeSucceeded($charge)
    {
        \Log::info('Charge succeeded', [
            'charge_id' => $charge->id,
            'payment_intent_id' => $charge->payment_intent,
            'amount' => $charge->amount,
        ]);

        // Find transaction that might be using payment intent ID as reference
        $transaction = \DB::table('lunar_transactions')
            ->where('reference', $charge->payment_intent)
            ->where('driver', 'stripe')
            ->first();

        if ($transaction) {
            // Extract card details
            $cardType = '';
            $lastFour = null;
            
            if (isset($charge->payment_method_details->card)) {
                $cardDetails = $charge->payment_method_details->card;
                $cardType = $cardDetails->brand ?? '';
                $lastFour = $cardDetails->last4 ?? null;
            }

            // Update meta to include charge information
            $meta = json_decode($transaction->meta, true) ?? [];
            $meta['charge_id'] = $charge->id;
            $meta['updated_by_webhook'] = now()->toISOString();
            unset($meta['needs_charge_id_update']);

            // Update transaction with proper charge ID and details
            \DB::table('lunar_transactions')
                ->where('id', $transaction->id)
                ->update([
                    'reference' => $charge->id, // This is the key fix
                    'card_type' => $cardType ?: $transaction->card_type,
                    'last_four' => $lastFour ?: $transaction->last_four,
                    'meta' => json_encode($meta),
                    'notes' => 'Payment processed via custom Stripe integration (updated by webhook)',
                    'updated_at' => now(),
                ]);

            \Log::info('Updated transaction with charge ID', [
                'transaction_id' => $transaction->id,
                'old_reference' => $charge->payment_intent,
                'new_reference' => $charge->id,
            ]);
        } else {
            \Log::info('No transaction found for payment intent', [
                'payment_intent_id' => $charge->payment_intent,
                'charge_id' => $charge->id,
            ]);
        }
    }
}