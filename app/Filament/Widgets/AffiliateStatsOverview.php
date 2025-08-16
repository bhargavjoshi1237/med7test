<?php

namespace App\Filament\Widgets;

use App\Models\Affiliate;
use App\Models\AffiliateActivity;
use App\Models\AffiliatePayout;
use Carbon\Carbon;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class AffiliateStatsOverview extends BaseWidget
{
    protected function getStats(): array
    {
        $totalAffiliates = Affiliate::count();
        $activeAffiliates = Affiliate::where('status', 'active')->count();
        
        $thisMonth = Carbon::now()->startOfMonth();
        $lastMonth = Carbon::now()->subMonth()->startOfMonth();
        
        $thisMonthCommission = AffiliateActivity::where('activity_date', '>=', $thisMonth)->sum('commission_amount');
        $lastMonthCommission = AffiliateActivity::whereBetween('activity_date', [$lastMonth, $thisMonth])->sum('commission_amount');
        
        $commissionChange = $lastMonthCommission > 0 
            ? (($thisMonthCommission - $lastMonthCommission) / $lastMonthCommission) * 100 
            : 0;
        
        $thisMonthActivities = AffiliateActivity::where('activity_date', '>=', $thisMonth)->count();
        $lastMonthActivities = AffiliateActivity::whereBetween('activity_date', [$lastMonth, $thisMonth])->count();
        
        $activitiesChange = $lastMonthActivities > 0 
            ? (($thisMonthActivities - $lastMonthActivities) / $lastMonthActivities) * 100 
            : 0;
        
        $pendingPayouts = AffiliatePayout::where('status', 'pending')->sum('amount');
        $totalPaidOut = AffiliatePayout::where('status', 'completed')->sum('amount');

        return [
            Stat::make('Total Affiliates', $totalAffiliates)
                ->description($activeAffiliates . ' active affiliates')
                ->descriptionIcon('heroicon-m-user-group')
                ->color('primary'),
            
            Stat::make('This Month Commission', '$' . number_format($thisMonthCommission, 2))
                ->description(($commissionChange >= 0 ? '+' : '') . number_format($commissionChange, 1) . '% from last month')
                ->descriptionIcon($commissionChange >= 0 ? 'heroicon-m-arrow-trending-up' : 'heroicon-m-arrow-trending-down')
                ->color($commissionChange >= 0 ? 'success' : 'danger'),
            
            Stat::make('This Month Activities', number_format($thisMonthActivities))
                ->description(($activitiesChange >= 0 ? '+' : '') . number_format($activitiesChange, 1) . '% from last month')
                ->descriptionIcon($activitiesChange >= 0 ? 'heroicon-m-arrow-trending-up' : 'heroicon-m-arrow-trending-down')
                ->color($activitiesChange >= 0 ? 'success' : 'danger'),
            
            Stat::make('Pending Payouts', '$' . number_format($pendingPayouts, 2))
                ->description('Total pending payments')
                ->descriptionIcon('heroicon-m-clock')
                ->color('warning'),
            
            Stat::make('Total Paid Out', '$' . number_format($totalPaidOut, 2))
                ->description('All-time payouts')
                ->descriptionIcon('heroicon-m-banknotes')
                ->color('success'),
        ];
    }
}