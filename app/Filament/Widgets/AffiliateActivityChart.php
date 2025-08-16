<?php

namespace App\Filament\Widgets;

use Filament\Widgets\ChartWidget;

class AffiliateActivityChart extends ChartWidget
{
    protected static ?string $heading = 'Affiliate Activity Chart';

    protected function getData(): array
    {
        return [
            'datasets' => [
                [
                    'label' => 'Activities',
                    'data' => [12, 19, 3, 5, 2, 3],
                    'backgroundColor' => 'rgba(54, 162, 235, 0.2)',
                    'borderColor' => 'rgba(54, 162, 235, 1)',
                    'borderWidth' => 1,
                ],
            ],
            'labels' => ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun'],
        ];
    }

    protected function getType(): string
    {
        return 'bar';
    }
}
