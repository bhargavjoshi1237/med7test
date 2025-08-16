<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AffiliateGroup extends Model
{
    use HasFactory;

    protected $guarded = [];

    public function affiliates()
    {
        return $this->belongsToMany(Affiliate::class, 'affiliate_group_affiliate');
    }

    /**
     * Calculate commission amount based on group settings
     */
    public function calculateCommission($orderValue)
    {
        if (!$this->rate || !$this->rate_type) {
            return 0;
        }

        if ($this->rate_type === 'percentage') {
            return ($orderValue * $this->rate) / 100;
        }
        
        return $this->rate;
    }
}
