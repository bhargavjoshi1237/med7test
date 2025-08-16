<?php

namespace App\Filament\Resources\DashboardResource\Widgets;

use App\Models\Affiliate;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Carbon;

class AffiliateActivityChart extends ChartWidget
{
    protected static ?string $heading = 'New Affiliate Registrations';

    protected function getData(): array
    {
        $data = [];
        $labels = [];

        for ($i = 6; $i >= 0; $i--) {
            $date = Carbon::now()->subDays($i);
            $labels[] = $date->format('M j');
            
            $registrations = Affiliate::whereDate('created_at', $date)->count();
            $data[] = $registrations;
        }

        return [
            'datasets' => [
                [
                    'label' => 'New Registrations',
                    'data' => $data,
                    'backgroundColor' => 'rgba(34, 197, 94, 0.8)',
                    'borderColor' => 'rgb(34, 197, 94)',
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