<?php

namespace App\Filament\Widgets;

use Leandrocfe\FilamentApexCharts\Widgets\ApexChartWidget;
use App\Models\AffiliateReferral;
use Carbon\Carbon;

class TrendsChart extends ApexChartWidget
{
    /**
     * This makes the widget span the full width of the content area.
     */
    protected int | string | array $columnSpan = 'full';

    /**
     * These properties MUST match the parent class declarations to avoid errors.
     */
    protected static ?string $chartId = 'trendsChart';
    protected static ?string $heading = 'Trends';

    /**
     * This holds the state of the filter dropdown for this widget instance.
     */
    public ?string $filter = 'this_month';

    /**
     * Defines the options for the chart's filter dropdown.
     */
    protected function getFilters(): ?array
    {
        return [
            'today' => 'Today',
            'yesterday' => 'Yesterday',
            'this_week' => 'This Week',
            'last_week' => 'Last Week',
            'this_month' => 'This Month',
            'last_month' => 'Last Month',
            'this_year' => 'This Year',
        ];
    }
    
    /**
     * This is the main method that builds the chart's data and options.
     */
    protected function getOptions(): array
    {
        [$fromDate, $toDate] = match ($this->filter) {
            'today' => [Carbon::today()->startOfDay(), Carbon::today()->endOfDay()],
            'yesterday' => [Carbon::yesterday()->startOfDay(), Carbon::yesterday()->endOfDay()],
            'this_week' => [Carbon::now()->startOfWeek(), Carbon::now()->endOfWeek()],
            'last_week' => [Carbon::now()->subWeek()->startOfWeek(), Carbon::now()->subWeek()->endOfWeek()],
            'last_month' => [Carbon::now()->subMonthNoOverflow()->startOfMonth(), Carbon::now()->subMonthNoOverflow()->endOfMonth()],
            'this_year' => [Carbon::now()->startOfYear(), Carbon::now()->endOfYear()],
            default => [Carbon::now()->startOfMonth(), Carbon::now()->endOfMonth()],
        };

        $referralsData = AffiliateReferral::query()
            ->whereBetween('created_at', [$fromDate, $toDate])
            ->selectRaw('DATE(created_at) as date, status, SUM(commission_amount) as total')
            ->groupBy('date', 'status')->orderBy('date')->get();

        $categories = $referralsData->pluck('date')->unique()->sort()->map(fn ($date) => Carbon::parse($date)->format('d M'))->toArray();
        $dateTemplate = array_fill_keys($referralsData->pluck('date')->unique()->sort()->toArray(), 0);

        $series = [];
        foreach (['paid', 'approved', 'pending', 'rejected'] as $status) {
            $statusData = $referralsData->where('status', $status)->pluck('total', 'date');
            $data = array_merge($dateTemplate, $statusData->toArray());
            $series[] = [
                'name' => ucfirst($status) . ' Earnings',
                'data' => array_map('round', array_values($data), array_fill(0, count($data), 2)),
            ];
        }

        return [
            'chart' => ['type' => 'line', 'height' => 300],
            'series' => $series,
            'xaxis' => ['categories' => $categories, 'labels' => ['style' => ['colors' => '#9ca3af', 'fontWeight' => 600]]],
            'yaxis' => ['labels' => ['style' => ['colors' => '#9ca3af', 'fontWeight' => 600], 'formatter' => 'function(val) { return "$" + val.toFixed(2) }']],
            'colors' => ['#22c55e', '#facc15', '#3b82f6', '#ef4444'],
            'stroke' => ['curve' => 'smooth', 'width' => 2],
            'dataLabels' => ['enabled' => false],
            'legend' => ['position' => 'top', 'horizontalAlign' => 'right'],
        ];
    }
}