<?php

namespace App\Livewire\Reports;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\AffiliateReferral;
use App\Models\Affiliate;
use Carbon\Carbon;

class ReferralsReport extends Component
{
    use WithPagination;

    // --- Main State ---
    public bool $showManageReferrals = false;

    // --- Dashboard Filters & Stats ---
    public string $dashboardDateRange = 'last_week';
    public string $dashboardAffiliateName = '';
    public float $paidEarningsAllTime = 0;
    public float $paidEarningsPeriod = 0;
    public float $unpaidEarningsPeriod = 0;
    public int $paidReferralsPeriod = 0;
    public int $unpaidReferralsPeriod = 0;
    public float $averageCommissionPeriod = 0;

    // --- Management Table Filters & Actions ---
    public string $statusFilter = '';
    public string $search = '';
    public array $selectedReferrals = [];
    public string $bulkAction = '';
    public string $tableAffiliateName = '';
    public $tableFromDate;
    public $tableToDate;

    public function mount()
    {
        $this->runDashboardCalculations();
    }
    
    public function updated($property)
    {
        // When a filter on the detailed table changes, reset pagination
        if (in_array($property, ['search', 'statusFilter', 'tableAffiliateName', 'tableFromDate', 'tableToDate'])) {
            $this->resetPage();
        }
    }
    
    public function runDashboardCalculations()
    {
        $this->resetPage(); // Reset pagination when dashboard filters change
        
        [$fromDate, $toDate] = match ($this->dashboardDateRange) {
            'this_week' => [Carbon::now()->startOfWeek(), Carbon::now()->endOfWeek()],
            'last_week' => [Carbon::now()->subWeek()->startOfWeek(), Carbon::now()->subWeek()->endOfWeek()],
            'this_month' => [Carbon::now()->startOfMonth(), Carbon::now()->endOfMonth()],
            'last_month' => [Carbon::now()->subMonthNoOverflow()->startOfMonth(), Carbon::now()->subMonthNoOverflow()->endOfMonth()],
            default => [null, null],
        };

        $baseQuery = AffiliateReferral::query()
            ->when($fromDate, fn($q) => $q->whereBetween('created_at', [$fromDate, $toDate]))
            ->when($this->dashboardAffiliateName, fn($q) => $q->whereHas('affiliate', fn($sub) => $sub->where('name', 'like', '%' . $this->dashboardAffiliateName . '%')));

        $statsData = (clone $baseQuery)->get();
        $this->paidEarningsPeriod = $statsData->where('status', 'paid')->sum('commission_amount');
        $this->unpaidEarningsPeriod = $statsData->where('status', 'approved')->sum('commission_amount');
        $this->paidReferralsPeriod = $statsData->where('status', 'paid')->count();
        $this->unpaidReferralsPeriod = $statsData->whereIn('status', ['approved', 'pending'])->count();
        $this->averageCommissionPeriod = $statsData->avg('commission_amount') ?? 0;
        $this->paidEarningsAllTime = AffiliateReferral::where('status', 'paid')->sum('commission_amount');
        
        $this->updateChartData($baseQuery);
    }
    
    public function updateChartData($baseQuery)
    {
        $referralsByDay = (clone $baseQuery)->selectRaw('DATE(created_at) as date, status, COUNT(*) as count')->groupBy('date', 'status')->orderBy('date')->get();
        $allDates = $referralsByDay->pluck('date')->unique()->sort();
        $dateTemplate = array_fill_keys($allDates->toArray(), 0);
        $labels = $allDates->map(fn ($date) => Carbon::parse($date)->format('M d'))->toArray();
        $datasets = [];
        foreach (['paid', 'approved', 'pending', 'rejected'] as $status) {
            $statusData = $referralsByDay->where('status', $status)->pluck('count', 'date');
            $data = array_merge($dateTemplate, $statusData->toArray());
            $datasets[] = ['label' => ucfirst($status) . ' Referrals', 'data' => array_values($data), 'borderColor' => match($status) {'paid' => '#4ade80', 'approved' => '#fde047', 'pending' => '#bae6fd', 'rejected' => '#f87171'}, 'tension' => 0.1];
        }
        $this->dispatch('trendsChartDataUpdated', ['labels' => $labels, 'datasets' => $datasets]);
    }

    public function toggleManageReferrals()
    {
        $this->showManageReferrals = ! $this->showManageReferrals;
    }

    public function applyBulkAction()
    {
        $referrals = AffiliateReferral::whereIn('id', $this->selectedReferrals);
        if ($this->bulkAction === 'mark_paid') $referrals->update(['status' => 'paid']);
        if ($this->bulkAction === 'reject') $referrals->update(['status' => 'rejected']);
        $this->selectedReferrals = [];
        $this->bulkAction = '';
    }

    public function render()
    {
        // Calculate status counts for the management table filter links
        $statusCounts = [
            'all' => AffiliateReferral::count(),
            'paid' => AffiliateReferral::where('status', 'paid')->count(),
            'unpaid' => AffiliateReferral::where('status', 'approved')->count(),
            'pending' => AffiliateReferral::where('status', 'pending')->count(),
            'rejected' => AffiliateReferral::where('status', 'rejected')->count(),
        ];

        // This query is ONLY for the detailed management table
        $referralsQuery = AffiliateReferral::query()
            ->when($this->tableFromDate, fn($q) => $q->whereDate('created_at', '>=', $this->tableFromDate))
            ->when($this->tableToDate, fn($q) => $q->whereDate('created_at', '<=', $this->tableToDate))
            ->when($this->tableAffiliateName, fn($q) => $q->whereHas('affiliate', fn($sub) => $sub->where('name', 'like', '%' . $this->tableAffiliateName . '%')))
            ->when($this->statusFilter, fn($q) => $q->where('status', $this->statusFilter))
            ->when($this->search, fn($q) => $q->where(fn($sub) => $sub->where('description', 'like', '%'.$this->search.'%')->orWhere('id', 'like', '%'.$this->search.'%')));

        return view('livewire.reports.referrals-report', [
            'referrals' => $this->showManageReferrals ? $referralsQuery->with('affiliate')->latest()->paginate(10) : collect(),
            'statusCounts' => $statusCounts,
        ]);
    }
}