<?php

namespace App\Filament\Resources\DashboardResource\Widgets;

use App\Models\AffiliatePayout;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class PayoutStatusWidget extends BaseWidget
{
    protected function getStats(): array
    {
        $pendingPayouts = AffiliatePayout::where('status', 'pending')->sum('amount');
        $paidPayouts = AffiliatePayout::where('status', 'paid')->sum('amount');
        $rejectedPayouts = AffiliatePayout::where('status', 'rejected')->sum('amount');
        
        $pendingCount = AffiliatePayout::where('status', 'pending')->count();
        $paidCount = AffiliatePayout::where('status', 'paid')->count();

        return [
            Stat::make('Pending Payouts', '$' . number_format($pendingPayouts, 2))
            ->description($pendingCount . ' transactions pending')
            ->descriptionIcon('heroicon-m-clock')
            ->color('warning'),
            
            Stat::make('Paid Commissions', '$' . number_format($paidPayouts, 2))
            ->description($paidCount . ' transactions completed')
            ->descriptionIcon('heroicon-m-check-circle')
            ->color('success'),
            
            Stat::make('Rejected/Refunded', '$' . number_format($rejectedPayouts, 2))
                ->description('Cancelled transactions')
                ->descriptionIcon('heroicon-m-x-circle')
                ->color('danger'),
        ];
    }
}