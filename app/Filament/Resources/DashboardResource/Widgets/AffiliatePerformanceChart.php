<?php

namespace App\Filament\Resources\DashboardResource\Widgets;

use App\Models\AffiliateActivity;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Carbon;

class AffiliatePerformanceChart extends ChartWidget
{
    protected static ?string $heading = 'Affiliate Performance (Last 12 Months)';

    protected function getData(): array
    {
        $data = [];
        $labels = [];

        for ($i = 11; $i >= 0; $i--) {
            $month = Carbon::now()->subMonths($i);
            $labels[] = $month->format('M Y');
            
            $commissions = AffiliateActivity::whereYear('activity_date', $month->year)
                ->whereMonth('activity_date', $month->month)
                ->sum('commission_amount');
            
            $data[] = $commissions;
        }

        return [
            'datasets' => [
                [
                    'label' => 'Commission Amount ($)',
                    'data' => $data,
                    'backgroundColor' => 'rgba(59, 130, 246, 0.1)',
                    'borderColor' => 'rgb(59, 130, 246)',
                    'borderWidth' => 2,
                    'fill' => true,
                ],
            ],
            'labels' => $labels,
        ];
    }

    protected function getType(): string
    {
        return 'line';
    }
}