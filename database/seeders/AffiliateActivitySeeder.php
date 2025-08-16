<?php

namespace Database\Seeders;

use App\Models\Affiliate;
use App\Models\AffiliateActivity;
use App\Models\AffiliateGroup;
use App\Models\AffiliateProductRate;
use App\Services\AffiliateCommissionService;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Lunar\Models\ProductVariant;

class AffiliateActivitySeeder extends Seeder
{
    public function run(): void
    {
        $commissionService = new AffiliateCommissionService();
        
        // Create sample affiliate groups with commission settings
        $percentageGroup = AffiliateGroup::create([
            'name' => 'Premium Affiliates',
            'rate_type' => 'percentage',
            'rate' => 15.00,
        ]);
        
        $flatRateGroup = AffiliateGroup::create([
            'name' => 'Flat Rate Partners',
            'rate_type' => 'flat',
            'rate' => 25.00,
        ]);
        
        // Get some affiliates and product variants (assuming they exist)
        $affiliates = Affiliate::take(3)->get();
        $productVariants = ProductVariant::take(5)->get();
        
        if ($affiliates->isEmpty() || $productVariants->isEmpty()) {
            $this->command->info('No affiliates or product variants found. Please create some first.');
            return;
        }
        
        // Create sample affiliate product rates
        foreach ($affiliates as $affiliate) {
            foreach ($productVariants->take(2) as $variant) {
                AffiliateProductRate::create([
                    'affiliate_id' => $affiliate->id,
                    'product_variant_id' => $variant->id,
                    'rate' => rand(10, 20),
                    'rate_type' => 'percentage',
                ]);
            }
        }
        
        // Create sample activities for the past 30 days
        foreach ($affiliates as $affiliate) {
            for ($i = 0; $i < rand(5, 15); $i++) {
                $variant = $productVariants->random();
                $price = rand(50, 500);
                $activityDate = Carbon::now()->subDays(rand(1, 30));
                
                $commissionService->recordActivity(
                    affiliateId: $affiliate->id,
                    productVariantId: $variant->id,
                    productPrice: $price,
                    buyerId: null,
                    orderReference: 'ORDER-' . rand(1000, 9999),
                    activityDate: $activityDate
                );
            }
        }
        
        $this->command->info('Sample affiliate activities created successfully!');
    }
}