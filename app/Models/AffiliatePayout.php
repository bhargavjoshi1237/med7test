<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AffiliatePayout extends Model
{
    use HasFactory;

    protected $guarded = [];

    protected $casts = [
        'referral_ids' => 'json',
        'paid_at' => 'datetime',
        'amount' => 'decimal:2',
    ];

    public function affiliate()
    {
        return $this->belongsTo(Affiliate::class);
    }
}
