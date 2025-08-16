<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Stripe\Stripe;
use Stripe\Charge;

class UpdateStripeChargeIds extends Command
{
    protected $signature = 'stripe:update-charge-ids {--dry-run : Show what would be updated without making changes}';
    protected $description = 'Update Stripe transactions that are using payment intent IDs instead of charge IDs';

    public function handle()
    {
        Stripe::setApiKey(env('STRIPE_SECRET'));
        
        $dryRun = $this->option('dry-run');
        
        if ($dryRun) {
            $this->info('Running in dry-run mode - no changes will be made');
        }

        // Find transactions that need updating (using payment intent IDs as reference)
        $transactions = DB::table('lunar_transactions')
            ->where('driver', 'stripe')
            ->where('reference', 'like', 'pi_%')
            ->get();

        if ($transactions->isEmpty()) {
            $this->info('No transactions found that need updating.');
            return;
        }

        $this->info("Found {$transactions->count()} transactions that may need updating.");

        $updated = 0;
        $failed = 0;

        foreach ($transactions as $transaction) {
            try {
                $paymentIntentId = $transaction->reference;
                
                $this->line("Processing transaction ID {$transaction->id} with payment intent {$paymentIntentId}");

                // Try to get charges for this payment intent
                $charges = Charge::all([
                    'payment_intent' => $paymentIntentId,
                    'limit' => 1
                ]);

                if ($charges->data && count($charges->data) > 0) {
                    $charge = $charges->data[0];
                    $chargeId = $charge->id;
                    
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
                    $meta['charge_id'] = $chargeId;
                    $meta['updated_by_command'] = now()->toISOString();
                    unset($meta['needs_charge_id_update']);

                    if (!$dryRun) {
                        DB::table('lunar_transactions')
                            ->where('id', $transaction->id)
                            ->update([
                                'reference' => $chargeId,
                                'card_type' => $cardType ?: $transaction->card_type,
                                'last_four' => $lastFour ?: $transaction->last_four,
                                'meta' => json_encode($meta),
                                'notes' => 'Payment processed via custom Stripe integration (updated by command)',
                                'updated_at' => now(),
                            ]);
                    }

                    $this->info("  ✓ Updated transaction {$transaction->id}: {$paymentIntentId} → {$chargeId}");
                    $updated++;
                } else {
                    $this->warn("  ✗ No charges found for payment intent {$paymentIntentId}");
                    $failed++;
                }

            } catch (\Exception $e) {
                $this->error("  ✗ Failed to update transaction {$transaction->id}: " . $e->getMessage());
                Log::error('Failed to update Stripe charge ID', [
                    'transaction_id' => $transaction->id,
                    'payment_intent_id' => $transaction->reference,
                    'error' => $e->getMessage()
                ]);
                $failed++;
            }
        }

        $this->newLine();
        
        if ($dryRun) {
            $this->info("Dry run completed. Would have updated {$updated} transactions.");
        } else {
            $this->info("Updated {$updated} transactions successfully.");
        }
        
        if ($failed > 0) {
            $this->warn("Failed to update {$failed} transactions.");
        }

        return 0;
    }
}