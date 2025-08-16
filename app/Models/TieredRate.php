<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TieredRate extends Model
{
    use HasFactory;

    protected $guarded = [];

    protected $casts = [
        'tiers' => 'json',
    ];

    public function affiliates()
    {
        return $this->hasMany(Affiliate::class);
    }
}
