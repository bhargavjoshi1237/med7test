<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AffiliateCreative extends Model
{
    use HasFactory;

    protected $guarded = [];

    protected $casts = [
        'starts_at' => 'datetime',
        'expires_at' => 'datetime',
    ];

    public function category()
    {
        return $this->belongsTo(CreativeCategory::class, 'category_id');
    }

    public function affiliates()
    {
        return $this->belongsToMany(Affiliate::class, 'affiliate_creative');
    }
}
