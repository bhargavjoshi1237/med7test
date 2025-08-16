<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AffiliateMinimumThreshold extends Model
{
    use HasFactory;

    protected $guarded = [];

    protected $casts = [
        'minimum_threshold' => 'decimal:2',
    ];

    public function affiliate()
    {
        return $this->belongsTo(Affiliate::class);
    }
}