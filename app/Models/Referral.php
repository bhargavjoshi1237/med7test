<?php

namespace App\Models\WPAffiliate;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Referral extends Model
{
    use HasFactory;

    protected $table = 'affiliate_referrals';

    protected $fillable = [
        'affiliate_id',
        'visit_id',
        'order_id',
        'description',
        'amount',
        'commission_amount',
        'currency',
        'custom',
        'status',
    ];

    public function affiliate()
    {
        return $this->belongsTo(Affiliate::class, 'affiliate_id');
    }

    public function visit()
    {
        return $this->belongsTo(Visit::class, 'visit_id');
    }
}
