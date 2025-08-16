<?php

namespace App\Filament\Resources\DashboardResource\Widgets;

use App\Models\Affiliate;
use App\Models\AffiliateActivity;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class AffiliateStatsOverview extends BaseWidget
{
    protected function getStats(): array
    {
        $totalAffiliates = Affiliate::count();
        $activeAffiliates = Affiliate::where('status', 'active')->count();
        $totalCommissions = AffiliateActivity::sum('commission_amount');
        $thisMonthCommissions = AffiliateActivity::whereMonth('activity_date', now()->month)
            ->whereYear('activity_date', now()->year)
            ->sum('commission_amount');

        return [
            Stat::make('Total Affiliates', $totalAffiliates)
                ->description('All registered affiliates')
                ->descriptionIcon('heroicon-m-users')
                ->color('primary'),
            
            Stat::make('Active Affiliates', $activeAffiliates)
                ->description('Currently active affiliates')
                ->descriptionIcon('heroicon-m-check-circle')
                ->color('success'),
            
            // Stat::make('Total Commissions', '$' . number_format($totalCommissions, 2))
            //     ->description('All time commission earnings')
            //     ->descriptionIcon('heroicon-m-currency-dollar')
            //     ->color('warning'),
            
            Stat::make('This Month', '$' . number_format($thisMonthCommissions, 2))
                ->description('Current month commissions')
                ->descriptionIcon('heroicon-m-calendar')
                ->color('info'),
        ];
    }
}