<?php

namespace App\Livewire\Reports;

use Livewire\Component;
use Lunar\Models\Order;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Symfony\Component\HttpFoundation\StreamedResponse;

class OrdersReport extends Component
{
    // Date filter state
    public string $dateRange = 'this_month';
    public ?string $fromDate = null;
    public ?string $toDate = null;

    // Computed stats
    public array $stats = [];
    public array $chartData = [];

    // Loading states
    public bool $isLoading = false;

    // THIS IS THE FIX: Replaced PHP 8 Attribute with the traditional $listeners property for universal compatibility.
    protected $listeners = ['export-csv' => 'exportCsv'];

    protected array $rules = [
        'fromDate' => 'nullable|date|before_or_equal:toDate',
        'toDate' => 'nullable|date|after_or_equal:fromDate',
    ];

    public function mount(): void
    {
        $this->initializeDates();
        $this->runCalculations();
    }

    public function updatedDateRange(): void
    {
        if ($this->dateRange !== 'custom') {
            $this->initializeDates();
        }
        $this->runCalculations();
    }

    public function updatedFromDate(): void
    {
        $this->dateRange = 'custom';
    }

    public function updatedToDate(): void
    {
        $this->dateRange = 'custom';
    }

    public function applyCustomDateRange(): void
    {
        $this->validate();
        $this->runCalculations();
    }

    private function initializeDates(): void
    {
        [$start, $end] = $this->getDateRangePeriod();
        $this->fromDate = $start?->format('Y-m-d');
        $this->toDate = $end?->format('Y-m-d');
    }

    private function getDateRangePeriod(): array
    {
        return match ($this->dateRange) {
            'year' => [Carbon::now()->startOfYear(), Carbon::now()->endOfYear()],
            'last_month' => [Carbon::now()->subMonthNoOverflow()->startOfMonth(), Carbon::now()->subMonthNoOverflow()->endOfMonth()],
            'this_month' => [Carbon::now()->startOfMonth(), Carbon::now()->endOfMonth()],
            'last_7_days' => [Carbon::now()->subDays(6)->startOfDay(), Carbon::now()->endOfDay()],
            'last_30_days' => [Carbon::now()->subDays(29)->startOfDay(), Carbon::now()->endOfDay()],
            'custom' => [
                $this->fromDate ? Carbon::parse($this->fromDate)->startOfDay() : null,
                $this->toDate ? Carbon::parse($this->toDate)->endOfDay() : null
            ],
            default => [Carbon::now()->startOfMonth(), Carbon::now()->endOfMonth()],
        };
    }

    public function runCalculations(): void
    {
        $this->isLoading = true;
        try {
            [$start, $end] = $this->getDateRangePeriod();
            if (!$start || !$end) { $this->resetStats(); return; }

            $orders = $this->getOrdersQuery($start, $end)->get();
            $this->calculateStats($orders);
            $this->prepareChartData($start, $end);
        } finally {
            $this->isLoading = false;
        }
    }

    private function getOrdersQuery(Carbon $start, Carbon $end): \Illuminate\Database\Eloquent\Builder
    {
        return Order::query()->whereNotNull('placed_at')->whereBetween('placed_at', [$start, $end])->with(['lines', 'transactions']);
    }

    private function calculateStats(Collection $orders): void
    {
        $this->stats = [
            'gross_sales' => $orders->sum('total.value') / 100,
            'net_sales' => $orders->sum('sub_total.value') / 100,
            'orders_placed' => $orders->count(),
            'items_purchased' => $orders->sum(fn($order) => $order->lines->sum('quantity')),
            'refunded_amount' => $orders->sum(fn($o) => $o->transactions->where('type', 'refund')->where('success', true)->sum('amount.value')) / 100,
            'shipping_charged' => $orders->sum('shipping_total.value') / 100,
            'coupons_used_value' => $orders->sum('discount_total.value') / 100,
            'average_order_value' => $orders->count() > 0 ? ($orders->sum('total.value') / 100) / $orders->count() : 0,
        ];
    }

    private function prepareChartData(Carbon $start, Carbon $end): void
    {
        $query = $this->getOrdersQuery($start, $end);
        $ordersByPeriod = $query->selectRaw('DATE(placed_at) as date, SUM(total / 100) as total_sales')->groupBy('date')->orderBy('date')->get();
        $this->chartData = [
            'labels' => $ordersByPeriod->pluck('date')->map(fn($date) => Carbon::parse($date)->format('M d'))->toArray(),
            'data' => $ordersByPeriod->pluck('total_sales')->toArray(),
        ];
        // THIS IS THE FIX: Use the standard dispatch method.
        $this->dispatch('salesChartDataUpdated', $this->chartData);
    }

    private function resetStats(): void
    {
        $this->stats = ['gross_sales' => 0, 'net_sales' => 0, 'orders_placed' => 0, 'items_purchased' => 0, 'refunded_amount' => 0, 'shipping_charged' => 0, 'coupons_used_value' => 0, 'average_order_value' => 0];
        $this->chartData = ['labels' => [], 'data' => []];
        $this->dispatch('salesChartDataUpdated', $this->chartData);
    }

    public function exportCsv(): StreamedResponse
    {
        [$start, $end] = $this->getDateRangePeriod();
        $filename = 'orders-report-' . now()->format('Y-m-d') . '.csv';

        return response()->streamDownload(function () use ($start, $end) {
            $file = fopen('php://output', 'w');
            fputcsv($file, ['ID', 'Reference', 'Status', 'Total ($)', 'Date']);
            
            $this->getOrdersQuery($start, $end)->chunk(200, function ($orders) use ($file) {
                foreach ($orders as $order) {
                    fputcsv($file, [$order->id, $order->reference, $order->status, $order->total->decimal, $order->placed_at->format('Y-m-d H:i')]);
                }
            });
            fclose($file);
        }, $filename);
    }

    public function getDateRangeOptions(): array
    {
        return [
            'last_7_days' => 'Last 7 days', 'last_30_days' => 'Last 30 days',
            'this_month' => 'This month', 'last_month' => 'Last month', 'year' => 'This year',
        ];
    }

    public function render()
    {
        return view('livewire.reports.orders-report');
    }
}