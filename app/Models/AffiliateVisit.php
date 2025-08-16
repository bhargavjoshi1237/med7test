<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AffiliateVisit extends Model
{
    use HasFactory;

    protected $guarded = [];

    public function affiliate()
    {
        return $this->belongsTo(Affiliate::class);
    }

    public function referral()
    {
        return $this->hasOne(AffiliateReferral::class, 'visit_id');
    }

    public function referrals()
    {
        return $this->hasMany(AffiliateReferral::class, 'visit_id');
    }
}
