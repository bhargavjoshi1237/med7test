<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Lunar\Models\Product;
use Lunar\Models\ProductVariant;

class AffiliateProductRate extends Model
{
    use HasFactory;

    protected $guarded = [];

    public function affiliate()
    {
        return $this->belongsTo(Affiliate::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * Get the actual product or variant based on the product_id format
     */
    public function getProductOrVariant()
    {
        if (str_starts_with($this->product_id, 'product_')) {
            $productId = str_replace('product_', '', $this->product_id);
            return Product::find($productId);
        } elseif (str_starts_with($this->product_id, 'variant_')) {
            $variantId = str_replace('variant_', '', $this->product_id);
            return ProductVariant::find($variantId);
        }
        
        return null;
    }

    /**
     * Check if this rate applies to a specific product or variant
     */
    public function appliesTo($productId = null, $variantId = null)
    {
        if ($variantId && str_starts_with($this->product_id, 'variant_')) {
            return str_replace('variant_', '', $this->product_id) == $variantId;
        }
        
        if ($productId && str_starts_with($this->product_id, 'product_')) {
            return str_replace('product_', '', $this->product_id) == $productId;
        }
        
        return false;
    }

    /**
     * Get the commission amount for a given order value
     */
    public function getCommissionAmount($orderValue)
    {
        if ($this->rate_type === 'percentage') {
            return ($orderValue * $this->rate) / 100;
        }
        
        return $this->rate;
    }
}
