<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CreativeCategory extends Model
{
    use HasFactory;

    protected $guarded = [];

    public function creatives()
    {
        return $this->hasMany(AffiliateCreative::class, 'category_id');
    }
}