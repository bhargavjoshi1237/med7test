<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\Affiliate;
use App\Models\AffiliateActivity;
use App\Models\AffiliatePayout;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class AffiliateDashboard extends Component
{
    public $paidEarnings = 0;
    public $unpaidEarnings = 0;
    public $totalReferrals = 0;
    public $pendingRegistrations = 0;
    public $monthlyEarnings = 0;
    public $todayEarnings = 0;

    public $earningsData = [];
    public $referralsData = [];
    public $affiliate;
    public $recentVisits = [];
    public $recentReferrals = [];
    public $recentPayouts = [];

    public function mount()
    {
        // Check if user is logged in
        if (!Auth::check()) {
            session(['url.intended' => request()->url()]);
            return $this->redirect(route('login'));
        }

        $user = Auth::user();

        // Check if user is an approved affiliate
        if (!$user->isApprovedAffiliate()) {
            return $this->redirect(route('affiliate.portal'));
        }

        $this->loadDashboardData();
    }

    public function loadDashboardData()
    {
        try {
            // Get the current user's affiliate record
            $user = Auth::user();
            $this->affiliate = $user->affiliate;

            if (!$this->affiliate) {
                // Initialize empty data for users without affiliate records
                $this->initializeEmptyData();
                return;
            }

            // Calculate paid earnings (commissions that have been paid out)
            $this->paidEarnings = $this->calculatePaidEarnings();

            // Calculate unpaid earnings (commissions that are pending)
            $this->unpaidEarnings = $this->calculateUnpaidEarnings();

            // Calculate total referrals
            $this->totalReferrals = $this->affiliate->referrals()->count();

            // Calculate total visits
            $this->pendingRegistrations = $this->affiliate->visits()->count();

            // Calculate monthly earnings
            $this->monthlyEarnings = $this->calculateMonthlyEarnings();

            // Calculate today's earnings
            $this->todayEarnings = $this->calculateTodayEarnings();

            // Prepare data for earnings chart
            $this->prepareEarningsChart();

            // Prepare data for referrals chart
            $this->prepareReferralsChart();

            // Load recent activity data
            $this->loadRecentActivity();
        } catch (\Exception $e) {
            // Log the error and initialize empty data
            \Log::error('Error loading affiliate dashboard data: ' . $e->getMessage());
            $this->initializeEmptyData();
        }
    }

    private function initializeEmptyData()
    {
        $this->paidEarnings = 0;
        $this->unpaidEarnings = 0;
        $this->totalReferrals = 0;
        $this->pendingRegistrations = 0;
        $this->monthlyEarnings = 0;
        $this->todayEarnings = 0;
        $this->earningsData = ['labels' => [], 'total' => [], 'paid' => []];
        $this->referralsData = ['labels' => ['Paid', 'Approved', 'Pending', 'Rejected'], 'data' => [0, 0, 0, 0]];
        $this->recentVisits = collect();
        $this->recentReferrals = collect();
        $this->recentPayouts = collect();
    }

    private function calculatePaidEarnings()
    {
        if (!$this->affiliate) return 0;

        // Sum all completed payouts
        $payoutTotal = $this->affiliate->payouts()
            ->where('status', 'completed')
            ->sum('amount') ?? 0;

        // Also include referrals marked as paid
        $referralTotal = $this->affiliate->referrals()
            ->where('status', 'paid')
            ->sum('commission_amount') ?? 0;

        // Return the higher value as a safety check
        return max($payoutTotal, $referralTotal);
    }

    private function calculateUnpaidEarnings()
    {
        if (!$this->affiliate) return 0;

        // Sum all approved and pending referrals that haven't been paid
        return $this->affiliate->referrals()
            ->whereIn('status', ['approved', 'pending'])
            ->sum('commission_amount') ?? 0;
    }

    private function calculateMonthlyEarnings()
    {
        if (!$this->affiliate) return 0;

        return $this->affiliate->referrals()
            ->whereMonth('created_at', now()->month)
            ->whereYear('created_at', now()->year)
            ->sum('commission_amount') ?? 0;
    }

    private function calculateTodayEarnings()
    {
        if (!$this->affiliate) return 0;

        return $this->affiliate->referrals()
            ->whereDate('created_at', now()->toDateString())
            ->sum('commission_amount') ?? 0;
    }

    private function prepareEarningsChart()
    {
        if (!$this->affiliate) {
            $this->earningsData = ['labels' => [], 'total' => [], 'paid' => []];
            return;
        }

        // Get earnings for the last 6 months
        $months = [];
        $earnings = [];
        $paidEarnings = [];

        for ($i = 5; $i >= 0; $i--) {
            $month = now()->subMonths($i);
            $monthName = $month->format('M');

            $monthlyEarnings = $this->affiliate->referrals()
                ->whereMonth('created_at', $month->month)
                ->whereYear('created_at', $month->year)
                ->sum('commission_amount') ?? 0;

            $monthlyPaid = $this->affiliate->referrals()
                ->whereMonth('created_at', $month->month)
                ->whereYear('created_at', $month->year)
                ->where('status', 'paid')
                ->sum('commission_amount') ?? 0;

            $months[] = $monthName;
            $earnings[] = round($monthlyEarnings, 2);
            $paidEarnings[] = round($monthlyPaid, 2);
        }

        $this->earningsData = [
            'labels' => $months,
            'total' => $earnings,
            'paid' => $paidEarnings
        ];
    }

    private function prepareReferralsChart()
    {
        if (!$this->affiliate) {
            $this->referralsData = [
                'labels' => ['Paid', 'Approved', 'Pending', 'Rejected'],
                'data' => [0, 0, 0, 0]
            ];
            return;
        }

        // Get referral status breakdown
        $paid = $this->affiliate->referrals()->where('status', 'paid')->count();
        $approved = $this->affiliate->referrals()->where('status', 'approved')->count();
        $pending = $this->affiliate->referrals()->where('status', 'pending')->count();
        $rejected = $this->affiliate->referrals()->where('status', 'rejected')->count();

        $this->referralsData = [
            'labels' => ['Paid', 'Approved', 'Pending', 'Rejected'],
            'data' => [$paid, $approved, $pending, $rejected]
        ];
    }

    private function loadRecentActivity()
    {
        if (!$this->affiliate) {
            $this->recentVisits = collect();
            $this->recentReferrals = collect();
            $this->recentPayouts = collect();
            return;
        }

        // Load recent visits (last 10)
        $this->recentVisits = $this->affiliate->visits()
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get()
            ->map(function ($visit) {
                return [
                    'url' => $visit->url ?? 'Direct Visit',
                    'created_at' => $visit->created_at,
                    'referrer' => $visit->referrer ?? 'Direct',
                    'converted' => $visit->converted ?? false
                ];
            });

        // Load recent referrals (last 10)
        $this->recentReferrals = $this->affiliate->referrals()
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get()
            ->map(function ($referral) {
                return [
                    'order_id' => $referral->order_id ?? 'N/A',
                    'commission_amount' => $referral->commission_amount ?? 0,
                    'status' => $referral->status ?? 'pending',
                    'created_at' => $referral->created_at
                ];
            });

        // Load recent payouts (last 5)
        $this->recentPayouts = $this->affiliate->payouts()
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get()
            ->map(function ($payout) {
                return [
                    'amount' => $payout->amount ?? 0,
                    'status' => $payout->status ?? 'pending',
                    'payment_method' => $payout->payment_method ?? 'N/A',
                    'payout_id' => $payout->payout_id ?? $payout->id,
                    'created_at' => $payout->created_at
                ];
            });
    }

    public function render()
    {
        return view('livewire.affiliate-dashboard', [
            'paidEarnings' => $this->paidEarnings,
            'unpaidEarnings' => $this->unpaidEarnings,
            'totalReferrals' => $this->totalReferrals,
            'pendingRegistrations' => $this->pendingRegistrations,
            'monthlyEarnings' => $this->monthlyEarnings,
            'todayEarnings' => $this->todayEarnings,
            'earningsData' => $this->earningsData,
            'referralsData' => $this->referralsData,
            'affiliate' => $this->affiliate,
            'recentVisits' => $this->recentVisits,
            'recentReferrals' => $this->recentReferrals,
            'recentPayouts' => $this->recentPayouts
        ])->layout('layouts.guest');
    }
}
