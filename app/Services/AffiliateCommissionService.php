<?php

namespace App\Services;

use App\Models\Affiliate;
use App\Models\AffiliateActivity;
use App\Models\AffiliateProductRate;
use Carbon\Carbon;
use Lunar\Models\ProductVariant;

class AffiliateCommissionService
{
    /**
     * Record affiliate activity and calculate commission
     */
    public function recordActivity(
        int $affiliateId,
        int $productVariantId,
        float $productPrice,
        ?int $buyerId = null,
        ?string $orderReference = null,
        ?Carbon $activityDate = null
    ): AffiliateActivity {
        $affiliate = Affiliate::findOrFail($affiliateId);
        $productVariant = ProductVariant::findOrFail($productVariantId);
        
        // Get commission rate from affiliate_product_rates table
        $productRate = AffiliateProductRate::where('affiliate_id', $affiliateId)
            ->where('product_variant_id', $productVariantId)
            ->first();
        
        if (!$productRate) {
            // Fall back to affiliate's default rate
            $commissionRate = $affiliate->rate ?? 0;
            $commissionType = $affiliate->rate_type ?? 'percentage';
        } else {
            $commissionRate = $productRate->rate;
            $commissionType = $productRate->rate_type;
        }
        
        // Calculate commission amount
        $commissionAmount = $this->calculateCommissionAmount($productPrice, $commissionRate, $commissionType);
        
        return AffiliateActivity::create([
            'affiliate_id' => $affiliateId,
            'product_variant_id' => $productVariantId,
            'buyer_id' => $buyerId,
            'product_price' => $productPrice,
            'commission_rate' => $commissionRate,
            'commission_type' => $commissionType,
            'commission_amount' => $commissionAmount,
            'order_reference' => $orderReference,
            'activity_date' => $activityDate ?? Carbon::now(),
        ]);
    }
    
    /**
     * Calculate commission amount based on rate and type
     */
    public function calculateCommissionAmount(float $productPrice, float $rate, string $rateType): float
    {
        if ($rateType === 'percentage') {
            return ($productPrice * $rate) / 100;
        }
        
        return $rate;
    }
    
    /**
     * Get pending commission for an affiliate since last payout
     */
    public function getPendingCommission(int $affiliateId, ?Carbon $fromDate = null, ?Carbon $toDate = null): array
    {
        $affiliate = Affiliate::findOrFail($affiliateId);
        
        if (!$fromDate) {
            $lastPayout = $affiliate->payouts()
                ->where('status', 'completed')
                ->latest('paid_at')
                ->first();
            
            $fromDate = $lastPayout ? $lastPayout->paid_at : Carbon::parse('2000-01-01');
        }
        
        $toDate = $toDate ?? Carbon::now();
        
        $activities = AffiliateActivity::getActivitiesForPayout($affiliateId, $fromDate, $toDate);
        $totalCommission = AffiliateActivity::calculateTotalCommission($affiliateId, $fromDate, $toDate);
        
        $minimumThreshold = $affiliate->getMinimumThreshold();
        $meetsThreshold = $affiliate->hasReachedMinimumThreshold($totalCommission);
        
        return [
            'from_date' => $fromDate,
            'to_date' => $toDate,
            'activities_count' => $activities->count(),
            'total_commission' => $totalCommission,
            'activities' => $activities,
            'last_payout' => $affiliate->payouts()->where('status', 'completed')->latest('paid_at')->first(),
            'minimum_threshold' => $minimumThreshold,
            'meets_threshold' => $meetsThreshold,
            'currency' => $affiliate->currency,
        ];
    }
    
    /**
     * Get commission rate for a specific affiliate and product variant
     */
    public function getCommissionRate(int $affiliateId, int $productVariantId): array
    {
        $productRate = AffiliateProductRate::where('affiliate_id', $affiliateId)
            ->where('product_variant_id', $productVariantId)
            ->first();
        
        if ($productRate) {
            return [
                'rate' => $productRate->rate,
                'rate_type' => $productRate->rate_type,
                'source' => 'product_specific'
            ];
        }
        
        $affiliate = Affiliate::findOrFail($affiliateId);
        
        return [
            'rate' => $affiliate->rate ?? 0,
            'rate_type' => $affiliate->rate_type ?? 'percentage',
            'source' => 'affiliate_default'
        ];
    }
}