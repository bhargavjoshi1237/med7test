<?php

namespace App\Models; // Changed from App\Models\WPAffiliate

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Affiliate extends Model
{
    use HasFactory;

    protected $guarded = [];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function parent()
    {
        return $this->belongsTo(Affiliate::class, 'parent_id');
    }

    public function children()
    {
        return $this->hasMany(Affiliate::class, 'parent_id');
    }

    public function visits()
    {
        return $this->hasMany(AffiliateVisit::class);
    }

    public function referrals()
    {
        return $this->hasMany(AffiliateReferral::class);
    }

    public function payouts()
    {
        return $this->hasMany(AffiliatePayout::class);
    }

    public function notes()
    {
        return $this->hasMany(AffiliateNote::class);
    }

    public function productRates()
    {
        return $this->hasMany(AffiliateProductRate::class);
    }

    public function creatives()
    {
        return $this->belongsToMany(AffiliateCreative::class, 'affiliate_creative');
    }

    public function landingPages()
    {
        return $this->hasMany(AffiliateLandingPage::class);
    }

    public function coupons()
    {
        return $this->hasMany(Coupon::class);
    }

    public function groups()
    {
        return $this->belongsToMany(AffiliateGroup::class, 'affiliate_group_affiliate');
    }

    public function tieredRate()
    {
        return $this->belongsTo(TieredRate::class);
    }

    public function tierRates()
    {
        return $this->hasMany(AffiliateTierRate::class);
    }

    public function activities()
    {
        return $this->hasMany(AffiliateActivity::class);
    }

    public function minimumThreshold()
    {
        return $this->hasOne(AffiliateMinimumThreshold::class);
    }

    public function currency()
    {
        return $this->belongsTo(\Lunar\Models\Currency::class);
    }

    /**
     * Get the minimum payout threshold for this affiliate
     */
    public function getMinimumThreshold()
    {
        return $this->minimumThreshold?->minimum_threshold ?? 0;
    }

    /**
     * Check if affiliate has reached minimum payout threshold
     */
    public function hasReachedMinimumThreshold($pendingAmount)
    {
        $threshold = $this->getMinimumThreshold();
        return $pendingAmount >= $threshold;
    }

    /**
     * Get the commission rate for a specific product or variant
     * Returns the specific rate if set, otherwise falls back to default rate
     */
    public function getCommissionRate($productId = null, $variantId = null)
    {
        // First, try to find a specific rate for the variant
        if ($variantId) {
            $variantRate = $this->productRates()
                ->where('product_id', "variant_{$variantId}")
                ->first();
            
            if ($variantRate) {
                return [
                    'rate' => $variantRate->rate,
                    'rate_type' => $variantRate->rate_type,
                    'source' => 'variant_specific'
                ];
            }
        }

        // Then, try to find a rate for the product
        if ($productId) {
            $productRate = $this->productRates()
                ->where('product_id', "product_{$productId}")
                ->first();
            
            if ($productRate) {
                return [
                    'rate' => $productRate->rate,
                    'rate_type' => $productRate->rate_type,
                    'source' => 'product_specific'
                ];
            }
        }

        // Fall back to default affiliate rate
        return [
            'rate' => $this->rate ?? 0,
            'rate_type' => $this->rate_type ?? 'percentage',
            'source' => 'default'
        ];
    }

    /**
     * Calculate commission amount for a given order value
     */
    public function calculateCommission($orderValue, $productId = null, $variantId = null)
    {
        $rateInfo = $this->getCommissionRate($productId, $variantId);
        
        if ($rateInfo['rate_type'] === 'percentage') {
            return ($orderValue * $rateInfo['rate']) / 100;
        }
        
        return $rateInfo['rate'];
    }
}
