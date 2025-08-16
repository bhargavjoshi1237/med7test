<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Lunar\Models\Discount;

class Coupon extends Model
{
    use HasFactory;

    protected $guarded = [];

    public function affiliate()
    {
        return $this->belongsTo(Affiliate::class);
    }

    public function discount()
    {
        return $this->hasOne(Discount::class, 'coupon', 'code');
    }
}
