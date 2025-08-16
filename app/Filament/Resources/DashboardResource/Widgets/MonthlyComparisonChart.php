<?php

namespace App\Filament\Resources\DashboardResource\Widgets;

use App\Models\AffiliateActivity;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Carbon;

class MonthlyComparisonChart extends ChartWidget
{
    protected static ?string $heading = 'Monthly Commission Comparison';

    protected function getData(): array
    {
        $currentYearData = [];
        $lastYearData = [];
        $labels = [];

        for ($i = 11; $i >= 0; $i--) {
            $currentMonth = Carbon::now()->subMonths($i);
            $lastYearMonth = Carbon::now()->subMonths($i)->subYear();
            
            $labels[] = $currentMonth->format('M');
            
            $currentYearCommissions = AffiliateActivity::whereYear('activity_date', $currentMonth->year)
                ->whereMonth('activity_date', $currentMonth->month)
                ->sum('commission_amount');
            
            $lastYearCommissions = AffiliateActivity::whereYear('activity_date', $lastYearMonth->year)
                ->whereMonth('activity_date', $lastYearMonth->month)
                ->sum('commission_amount');
            
            $currentYearData[] = $currentYearCommissions;
            $lastYearData[] = $lastYearCommissions;
        }

        return [
            'datasets' => [
                [
                    'label' => Carbon::now()->year . ' Commissions',
                    'data' => $currentYearData,
                    'backgroundColor' => 'rgba(59, 130, 246, 0.8)',
                    'borderColor' => 'rgb(59, 130, 246)',
                    'borderWidth' => 1,
                ],
                [
                    'label' => (Carbon::now()->year - 1) . ' Commissions',
                    'data' => $lastYearData,
                    'backgroundColor' => 'rgba(156, 163, 175, 0.8)',
                    'borderColor' => 'rgb(156, 163, 175)',
                    'borderWidth' => 1,
                ],
            ],
            'labels' => $labels,
        ];
    }

    protected function getType(): string
    {
        return 'bar';
    }
}