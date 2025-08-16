<?php

namespace App\Filament\Resources\DashboardResource\Widgets;

use App\Models\AffiliateActivity;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Carbon;

class AffiliateCommissionOverview extends BaseWidget
{
    protected function getStats(): array
    {
        $thisMonth = AffiliateActivity::whereMonth('activity_date', Carbon::now()->month)
            ->whereYear('activity_date', Carbon::now()->year)
            ->sum('commission_amount');

        $lastMonth = AffiliateActivity::whereMonth('activity_date', Carbon::now()->subMonth()->month)
            ->whereYear('activity_date', Carbon::now()->subMonth()->year)
            ->sum('commission_amount');

        $growth = $lastMonth > 0 ? (($thisMonth - $lastMonth) / $lastMonth) * 100 : 0;

        $avgCommission = AffiliateActivity::avg('commission_amount') ?? 0;
        $highestCommission = AffiliateActivity::max('commission_amount') ?? 0;

        return [
            Stat::make('This Month', '$' . number_format($thisMonth, 2))
                ->description(($growth >= 0 ? '+' : '') . number_format($growth, 1) . '% from last month')
                ->descriptionIcon($growth >= 0 ? 'heroicon-m-arrow-trending-up' : 'heroicon-m-arrow-trending-down')
                ->color($growth >= 0 ? 'success' : 'danger'),
            
            Stat::make('Average Commission', '$' . number_format($avgCommission, 2))
                ->description('Per successful referral')
                ->descriptionIcon('heroicon-m-calculator')
                ->color('info'),
            
            Stat::make('Highest Commission', '$' . number_format($highestCommission, 2))
                ->description('Single highest earning')
                ->descriptionIcon('heroicon-m-trophy')
                ->color('warning'),
        ];
    }
}