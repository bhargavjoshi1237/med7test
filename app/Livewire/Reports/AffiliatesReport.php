<?php

namespace App\Livewire\Reports;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\Affiliate;
use Carbon\Carbon;

class AffiliatesReport extends Component
{
    use WithPagination;

    // --- Main State ---
    public bool $showManageAffiliates = false;

    // --- Dashboard Filters & Stats ---
    public string $dashboardDateRange = 'this_month';
    public int $totalAffiliates = 0;
    public int $newAffiliatesCount = 0;
    public ?string $topEarningAffiliateName = null;
    public ?string $highestConvertingAffiliateName = null;

    // --- Management Table Filters & Actions ---
    public string $statusFilter = '';
    public string $search = '';
    public array $selectedAffiliates = [];
    public string $bulkAction = '';

    public function mount()
    {
        $this->runDashboardCalculations();
    }

    public function updated($property)
    {
        if (in_array($property, ['search', 'statusFilter'])) {
            $this->resetPage();
        }
    }

    public function runDashboardCalculations()
    {
        $this->resetPage();
        [$fromDate, $toDate] = match ($this->dashboardDateRange) {
            'this_week' => [Carbon::now()->startOfWeek(), Carbon::now()->endOfWeek()],
            'last_week' => [Carbon::now()->subWeek()->startOfWeek(), Carbon::now()->subWeek()->endOfWeek()],
            'this_month' => [Carbon::now()->startOfMonth(), Carbon::now()->endOfMonth()],
            'last_month' => [Carbon::now()->subMonthNoOverflow()->startOfMonth(), Carbon::now()->subMonthNoOverflow()->endOfMonth()],
            'this_year' => [Carbon::now()->startOfYear(), Carbon::now()->endOfYear()],
            default => [Carbon::now()->startOfMonth(), Carbon::now()->endOfMonth()],
        };

        $this->totalAffiliates = Affiliate::count();
        $this->newAffiliatesCount = Affiliate::whereBetween('created_at', [$fromDate, $toDate])->count();
        
        $topEarner = Affiliate::query()->withSum(['referrals' => fn($q) => $q->where('status', 'paid')->whereBetween('created_at', [$fromDate, $toDate])], 'commission_amount')->orderByDesc('referrals_sum_commission_amount')->first();
        $this->topEarningAffiliateName = ($topEarner && $topEarner->referrals_sum_commission_amount > 0) ? $topEarner->name : null;
        
        $topConverter = Affiliate::query()->withCount(['referrals' => fn($q) => $q->whereBetween('created_at', [$fromDate, $toDate])])->orderByDesc('referrals_count')->first();
        $this->highestConvertingAffiliateName = ($topConverter && $topConverter->referrals_count > 0) ? $topConverter->name : null;

        $this->updateChartData($fromDate, $toDate);
    }
    
    public function updateChartData($fromDate, $toDate)
    {
        $registrations = Affiliate::query()
            ->when($fromDate, fn($q) => $q->whereBetween('created_at', [$fromDate, $toDate]))
            ->selectRaw('DATE(created_at) as date, COUNT(*) as count')
            ->groupBy('date')->orderBy('date')->get();
            
        $labels = $registrations->pluck('date')->map(fn ($date) => Carbon::parse($date)->format('M d'))->toArray();
        $data = $registrations->pluck('count')->toArray();
        
        $this->dispatch('affiliateChartDataUpdated', ['labels' => $labels, 'data' => $data]);
    }

    public function toggleManageAffiliates()
    {
        $this->showManageAffiliates = ! $this->showManageAffiliates;
    }

    public function applyBulkAction()
    {
        $affiliates = Affiliate::whereIn('id', $this->selectedAffiliates);
        if ($this->bulkAction === 'approve') $affiliates->update(['status' => 'active']);
        if ($this->bulkAction === 'reject') $affiliates->update(['status' => 'rejected']);
        $this->selectedAffiliates = [];
        $this->bulkAction = '';
    }

    public function render()
    {
        $statusCounts = [
            'all' => Affiliate::count(),
            'active' => Affiliate::where('status', 'active')->count(),
            'inactive' => Affiliate::where('status', 'inactive')->count(),
            'pending' => Affiliate::where('status', 'pending')->count(),
            'rejected' => Affiliate::where('status', 'rejected')->count(),
        ];

        $affiliatesQuery = Affiliate::query()
            ->withSum('referrals as paid_earnings', 'commission_amount')
            ->withSum(['referrals as unpaid_earnings' => fn($q) => $q->where('status', 'approved')], 'commission_amount')
            ->withCount(['referrals as unpaid_referrals_count' => fn($q) => $q->where('status', 'approved')])
            ->withCount(['referrals as paid_referrals_count' => fn($q) => $q->where('status', 'paid')])
            ->withCount('visits')
            ->when($this->statusFilter, fn($q) => $q->where('status', $this->statusFilter))
            ->when($this->search, fn($q) => $q->where(fn($sub) => $sub->where('name', 'like', '%'.$this->search.'%')->orWhere('id', 'like', '%'.$this->search.'%')));

        return view('livewire.reports.affiliates-report', [
            'affiliates' => $this->showManageAffiliates ? $affiliatesQuery->latest()->paginate(10) : collect(),
            'statusCounts' => $statusCounts,
        ]);
    }
}