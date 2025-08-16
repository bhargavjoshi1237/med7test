<?php

namespace App\Livewire\Reports;

use Livewire\Component;
use Livewire\WithPagination;
use Livewire\Attributes\On;
use Livewire\Attributes\Computed;
use Lunar\Models\Customer;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class CustomersReport extends Component
{
    use WithPagination;

    // View state management
    public bool $showCustomerList = false;
    public bool $isLoading = false;

    // Dashboard filters
    public string $dateRange = 'this_month';
    public ?string $fromDate = null;
    public ?string $toDate = null;

    // Customer list filters
    public string $search = '';
    public string $sortBy = 'created_at';
    public string $sortDirection = 'desc';

    // Dashboard stats
    public array $stats = [];
    public array $chartData = [];

    protected array $rules = [
        'fromDate' => 'nullable|date|before_or_equal:toDate',
        'toDate' => 'nullable|date|after_or_equal:fromDate',
        'search' => 'string|max:255',
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
        $this->validateOnly('fromDate');
        $this->runCalculations();
    }

    public function updatedToDate(): void
    {
        $this->dateRange = 'custom';
        $this->validateOnly('toDate');
        $this->runCalculations();
    }

    public function updatedSearch(): void
    {
        $this->resetPage();
        $this->validateOnly('search');
    }

    public function toggleCustomerList(): void
    {
        $this->showCustomerList = !$this->showCustomerList;
        $this->resetPage();
    }

    public function sortBy(string $field): void
    {
        if ($this->sortBy === $field) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortBy = $field;
            $this->sortDirection = 'asc';
        }
        $this->resetPage();
    }

    #[On('loadCustomerChartData')]
    public function runCalculations(): void
    {
        $this->isLoading = true;

        try {
            [$start, $end] = $this->getDateRange();
            
            if (!$start || !$end) {
                $this->resetStats();
                return;
            }

            $this->calculateDashboardStats($start, $end);
            $this->prepareChartData($start, $end);
            
        } catch (\Exception $e) {
            $this->resetStats();
            session()->flash('error', 'Unable to load customer data. Please try again.');
        } finally {
            $this->isLoading = false;
        }
    }

    private function initializeDates(): void
    {
        [$start, $end] = $this->getDateRange();
        $this->fromDate = $start?->format('Y-m-d');
        $this->toDate = $end?->format('Y-m-d');
    }

    private function getDateRange(): array
    {
        return match ($this->dateRange) {
            'year' => [Carbon::now()->startOfYear(), Carbon::now()->endOfYear()],
            'last_month' => [
                Carbon::now()->subMonthNoOverflow()->startOfMonth(),
                Carbon::now()->subMonthNoOverflow()->endOfMonth()
            ],
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

    private function calculateDashboardStats(Carbon $start, Carbon $end): void
    {
        $query = $this->getCustomersQuery($start, $end);
        
        $customers = $query->get();
        $totalCustomers = Customer::count();
        $previousPeriod = $this->getPreviousPeriodStats($start, $end);

        $this->stats = [
            'new_signups' => $customers->count(),
            'total_customers' => $totalCustomers,
            'growth_rate' => $this->calculateGrowthRate($customers->count(), $previousPeriod['signups']),
            'average_signups_per_day' => $this->calculateDailyAverage($customers->count(), $start, $end),
            'customers_with_orders' => $this->getCustomersWithOrders($start, $end),
            'conversion_rate' => $this->calculateConversionRate($customers->count()),
        ];
    }

    private function getPreviousPeriodStats(Carbon $start, Carbon $end): array
    {
        $periodLength = $start->diffInDays($end);
        $previousStart = $start->copy()->subDays($periodLength + 1);
        $previousEnd = $start->copy()->subDay();

        $previousQuery = $this->getCustomersQuery($previousStart, $previousEnd);
        
        return [
            'signups' => $previousQuery->count(),
        ];
    }

    private function calculateGrowthRate(int $current, int $previous): float
    {
        if ($previous === 0) {
            return $current > 0 ? 100 : 0;
        }
        
        return round((($current - $previous) / $previous) * 100, 1);
    }

    private function calculateDailyAverage(int $signups, Carbon $start, Carbon $end): float
    {
        $days = max(1, $start->diffInDays($end) + 1);
        return round($signups / $days, 1);
    }

    private function getCustomersWithOrders(Carbon $start, Carbon $end): int
    {
        return Customer::whereHas('orders', function ($query) use ($start, $end) {
            $query->whereNotNull('placed_at')
                  ->whereBetween('placed_at', [$start, $end]);
        })->count();
    }

    private function calculateConversionRate(int $newSignups): float
    {
        if ($newSignups === 0) return 0;
        
        $customersWithOrders = Customer::whereHas('orders')->count();
        $totalCustomers = Customer::count();
        
        return $totalCustomers > 0 ? round(($customersWithOrders / $totalCustomers) * 100, 1) : 0;
    }

    private function getCustomersQuery(Carbon $start, Carbon $end): \Illuminate\Database\Eloquent\Builder
    {
        return Customer::query()
            ->whereBetween('created_at', [$start, $end]);
    }

    private function prepareChartData(Carbon $start, Carbon $end): void
    {
        $query = $this->getCustomersQuery($start, $end);
        
        $signupsByPeriod = $query
            ->selectRaw($this->getGroupByExpression() . ', COUNT(*) as signups_count')
            ->groupByRaw($this->getGroupByExpression())
            ->orderByRaw($this->getGroupByExpression())
            ->get();

        $this->chartData = [
            'labels' => $signupsByPeriod->pluck('period')->map(fn($date) => $this->formatChartLabel($date))->toArray(),
            'data' => $signupsByPeriod->pluck('signups_count')->toArray(),
        ];

        $this->dispatch('customerChartDataUpdated', ['data' => $this->chartData]);
    }

    private function getGroupByExpression(): string
    {
        $daysDiff = Carbon::parse($this->fromDate)->diffInDays(Carbon::parse($this->toDate));
        
        return match (true) {
            $daysDiff <= 31 => 'DATE(created_at) as period',
            $daysDiff <= 365 => 'DATE_FORMAT(created_at, "%Y-%m") as period',
            default => 'DATE_FORMAT(created_at, "%Y") as period',
        };
    }

    private function formatChartLabel(string $date): string
    {
        $daysDiff = Carbon::parse($this->fromDate)->diffInDays(Carbon::parse($this->toDate));
        
        return match (true) {
            $daysDiff <= 31 => Carbon::parse($date)->format('M d'),
            $daysDiff <= 365 => Carbon::parse($date . '-01')->format('M Y'),
            default => $date,
        };
    }

    private function resetStats(): void
    {
        $this->stats = [
            'new_signups' => 0,
            'total_customers' => 0,
            'growth_rate' => 0,
            'average_signups_per_day' => 0,
            'customers_with_orders' => 0,
            'conversion_rate' => 0,
        ];
        
        $this->chartData = ['labels' => [], 'data' => []];
        $this->dispatch('customerChartDataUpdated', ['data' => $this->chartData]);
    }

    #[Computed]
    public function customers(): LengthAwarePaginator
    {
        if (!$this->showCustomerList) {
            return new \Illuminate\Pagination\LengthAwarePaginator(
                collect([]), 0, 15, 1
            );
        }

        return Customer::query()
            ->withCount(['orders' => fn($q) => $q->whereNotNull('placed_at')])
            ->withSum(['orders' => fn($q) => $q->whereNotNull('placed_at')], 'total')
            ->with('users:id,email')
            ->when($this->search, function ($query) {
                $search = '%' . $this->search . '%';
                return $query->where(function ($q) use ($search) {
                    $q->where('first_name', 'like', $search)
                      ->orWhere('last_name', 'like', $search)
                      ->orWhereRaw("CONCAT(first_name, ' ', last_name) LIKE ?", [$search])
                      ->orWhereHas('users', fn($userQuery) => $userQuery->where('email', 'like', $search));
                });
            })
            ->orderBy($this->sortBy, $this->sortDirection)
            ->paginate(15, ['*'], 'customers');
    }

    public function getDateRangeOptions(): array
    {
        return [
            'last_7_days' => 'Last 7 days',
            'last_30_days' => 'Last 30 days', 
            'this_month' => 'This month',
            'last_month' => 'Last month',
            'year' => 'This year',
            'custom' => 'Custom range',
        ];
    }

    #[On('export-customers-csv')]
    public function exportCustomersCsv(): void
    {
        $customers = Customer::query()
            ->withCount(['orders' => fn($q) => $q->whereNotNull('placed_at')])
            ->withSum(['orders' => fn($q) => $q->whereNotNull('placed_at')], 'total')
            ->with('users:id,email')
            ->when($this->search, function ($query) {
                $search = '%' . $this->search . '%';
                return $query->where(function ($q) use ($search) {
                    $q->where('first_name', 'like', $search)
                      ->orWhere('last_name', 'like', $search)
                      ->orWhereRaw("CONCAT(first_name, ' ', last_name) LIKE ?", [$search])
                      ->orWhereHas('users', fn($userQuery) => $userQuery->where('email', 'like', $search));
                });
            })
            ->get();

        $exportData = $customers->map(fn($customer) => [
            'Name' => $customer->fullName,
            'Email' => $customer->users->first()?->email ?? 'N/A',
            'Orders Count' => $customer->orders_count ?? 0,
            'Total Spend' => number_format(($customer->orders_sum_total ?? 0) / 100, 2),
            'Registration Date' => $customer->created_at->format('Y-m-d H:i:s'),
        ])->toArray();

        $this->dispatch('download-customers-csv', [
            'filename' => 'customers-report-' . now()->format('Y-m-d') . '.csv',
            'data' => $exportData
        ]);
    }

    public function render()
    {
        return view('livewire.reports.customers-report');
    }
}