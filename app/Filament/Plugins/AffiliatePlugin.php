<?php

namespace App\Filament\Plugins;

use App\Filament\Pages\Reports;
use App\Filament\Pages\WooCommerceReport;
use App\Filament\Resources\AffiliateCreativeResource;
use App\Filament\Resources\AffiliateGroupResource;
use App\Filament\Resources\AffiliatePayoutResource;
use App\Filament\Resources\AffiliateReferralResource;
use App\Filament\Resources\AffiliateResource;
use App\Filament\Resources\AffiliateVisitResource;
use App\Filament\Resources\CouponResource;
use App\Filament\Resources\CreativeCategoryResource;
use App\Filament\Resources\TieredRateResource;
use App\Filament\Resource\AffiliateDashboard;
use App\Filament\Resources\DashboardResource;
use App\Filament\Resources\RefundRequestResource;
use Filament\Contracts\Plugin;
use Filament\Panel;

class AffiliatePlugin implements Plugin
{
    public function getId(): string
    {
        return 'affiliate';
    }

    public function register(Panel $panel): void
    {
        $panel
            ->pages([
                Reports::class,
                WooCommerceReport::class,
            ])
            ->resources([
                DashboardResource::class,
                AffiliateResource::class,
                    // AffiliateCreativeResource::class,
                AffiliateGroupResource::class,
                AffiliatePayoutResource::class,
                AffiliateReferralResource::class,
                AffiliateVisitResource::class,
                    // CreativeCategoryResource::class,
                TieredRateResource::class,
                RefundRequestResource::class,
                CouponResource::class,
            ])
            ->maxContentWidth('full');
    }

    public function boot(Panel $panel): void
    {
        //
    }

    public static function make(): static
    {
        return app(static::class);
    }
}
