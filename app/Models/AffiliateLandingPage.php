<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AffiliateLandingPage extends Model
{
    use HasFactory;

    protected $guarded = [];

    public function affiliate()
    {
        return $this->belongsTo(Affiliate::class);
    }
}
