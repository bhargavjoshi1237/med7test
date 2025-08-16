<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Affiliate;
use App\Services\AffiliateCommissionService;
use Lunar\Models\Product;
use Lunar\Models\ProductVariant;

class TestAffiliateCommissions extends Command
{
    protected $signature = 'affiliate:test-commissions';
    protected $description = 'Test affiliate commission calculations with products and variants';

    public function handle()
    {
        $this->info('Testing Affiliate Commission System');
        $this->line('=====================================');

        // Get first affiliate
        $affiliate = Affiliate::first();
        if (!$affiliate) {
            $this->error('No affiliates found. Please create an affiliate first.');
            return;
        }

        $this->info("Testing with Affiliate: {$affiliate->name} (ID: {$affiliate->id})");
        $this->info("Default Rate: {$affiliate->rate}% ({$affiliate->rate_type})");
        $this->line('');

        // Get some products with variants
        $products = Product::with('variants')->take(3)->get();
        
        if ($products->isEmpty()) {
            $this->error('No products found. Please seed some products first.');
            return;
        }

        $commissionService = new AffiliateCommissionService();

        foreach ($products as $product) {
            $this->info("Product: {$product->translateAttribute('name')} (ID: {$product->id})");
            
            // Test base product commission
            $preview = $commissionService->getCommissionPreview($affiliate, $product->id, null, 100);
            $this->line("  Base Product Commission: {$preview['formatted_rate']} = \${$preview['commission_amount']} (Source: {$preview['rate_source']})");

            // Test variant commissions
            foreach ($product->variants as $variant) {
                $variantName = "Variant #{$variant->id}";
                if ($variant->values && $variant->values->count() > 0) {
                    $variantDetails = $variant->values->map(function ($value) {
                        return $value->translate('name');
                    })->join(', ');
                    $variantName .= " ({$variantDetails})";
                }

                $preview = $commissionService->getCommissionPreview($affiliate, $product->id, $variant->id, 100);
                $this->line("    {$variantName}: {$preview['formatted_rate']} = \${$preview['commission_amount']} (Source: {$preview['rate_source']})");
            }
            
            $this->line('');
        }

        $this->info('Commission test completed!');
        $this->line('');
        $this->info('To set specific rates:');
        $this->info('1. Go to Admin Panel > Affiliates');
        $this->info('2. Edit an affiliate');
        $this->info('3. Go to "Product Rates" tab');
        $this->info('4. Add rates for specific products or variants');
    }
}