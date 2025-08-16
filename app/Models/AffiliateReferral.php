<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AffiliateReferral extends Model
{
    use HasFactory;

    protected $guarded = [];
    
    protected $fillable = [
        'affiliate_id',
        'visit_id',
        'order_id',
        'amount',
        'commission_amount',
        'commission_rate',
        'commission_type',
        'status',
        'parent_referral_id',
        'currency',
        'notes',
        'description'
    ];

    public function affiliate()
    {
        return $this->belongsTo(Affiliate::class);
    }

    public function visit()
    {
        return $this->belongsTo(AffiliateVisit::class, 'visit_id');
    }

    public function parentReferral()
    {
        return $this->belongsTo(AffiliateReferral::class, 'parent_referral_id');
    }

    public function childReferrals()
    {
        return $this->hasMany(AffiliateReferral::class, 'parent_referral_id');
    }
}
