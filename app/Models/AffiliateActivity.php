<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Lunar\Models\ProductVariant;

class AffiliateActivity extends Model
{
    use HasFactory;

    protected $table = 'affiliate_activity';
    protected $guarded = [];

    protected $casts = [
        'activity_date' => 'datetime',
        'product_price' => 'decimal:2',
        'commission_rate' => 'decimal:2',
        'commission_amount' => 'decimal:2',
    ];

    public function affiliate()
    {
        return $this->belongsTo(Affiliate::class);
    }

    public function productVariant()
    {
        return $this->belongsTo(ProductVariant::class);
    }

    public function buyer()
    {
        return $this->belongsTo(User::class, 'buyer_id');
    }

    /**
     * Get activities for an affiliate within a date range
     */
    public static function getActivitiesForPayout($affiliateId, $fromDate, $toDate = null)
    {
        $query = static::where('affiliate_id', $affiliateId)
            ->where('activity_date', '>=', $fromDate);

        if ($toDate) {
            $query->where('activity_date', '<=', $toDate);
        }

        return $query->orderBy('activity_date', 'desc')->get();
    }

    /**
     * Calculate total commission for an affiliate within a date range
     */
    public static function calculateTotalCommission($affiliateId, $fromDate, $toDate = null)
    {
        $query = static::where('affiliate_id', $affiliateId)
            ->where('activity_date', '>=', $fromDate);

        if ($toDate) {
            $query->where('activity_date', '<=', $toDate);
        }

        return $query->sum('commission_amount');
    }
}