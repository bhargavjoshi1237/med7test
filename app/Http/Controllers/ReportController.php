<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\Affiliate;
use App\Models\AffiliateReferral;
use App\Models\AffiliatePayout;
use App\Models\AffiliateVisit;

class ReportController extends Controller
{
    /**
     * Base method to get filter inputs and all affiliates for dropdowns.
     */
    private function getReportFilters(Request $request)
    {
        return [
            'all_affiliates' => DB::table('affiliates')->select('id', 'name')->orderBy('name')->get(),
            'selected_affiliates' => $request->input('affiliates', []),
            'from_date' => $request->input('from_date'),
            'to_date' => $request->input('to_date'),
        ];
    }

    /**
     * Referrals Report
     */
    public function referrals(Request $request)
    {
        $filters = $this->getReportFilters($request);

        $query = DB::table('affiliate_referrals as ar')
            ->join('affiliates as a', 'ar.affiliate_id', '=', 'a.id')
            ->select(
                'ar.id as referral_id',
                'ar.commission_amount as amount',
                'a.name as affiliate_name',
                'ar.order_id as reference',
                'ar.description',
                'ar.commission_type as type',
                'ar.created_at as date',
                'ar.status'
            );

        if (!empty($filters['selected_affiliates'])) {
            $query->whereIn('ar.affiliate_id', $filters['selected_affiliates']);
        }
        if ($filters['from_date']) {
            $query->where('ar.created_at', '>=', $filters['from_date']);
        }
        if ($filters['to_date']) {
            $query->where('ar.created_at', '<=', $filters['to_date']);
        }

        $referrals = $query->orderBy('ar.created_at', 'desc')->paginate(20);

        return view('admin.reports.referrals', compact('referrals', 'filters'));
    }

    /**
     * Affiliates Report
     */
    public function affiliates(Request $request)
    {
        $filters = $this->getReportFilters($request);

        $query = DB::table('affiliates')
            ->select(
                'id',
                'name',
                'email',
                'status',
                DB::raw("CONCAT(rate, ' ', rate_type) as commission_rate"),
                'store_credit_balance',
                'created_at as registration_date'
            );

        if (!empty($filters['selected_affiliates'])) {
            $query->whereIn('id', $filters['selected_affiliates']);
        }
        if ($filters['from_date']) {
            $query->where('created_at', '>=', $filters['from_date']);
        }
        if ($filters['to_date']) {
            $query->where('created_at', '<=', $filters['to_date']);
        }

        $affiliates = $query->orderBy('created_at', 'desc')->paginate(20);

        return view('admin.reports.affiliates', compact('affiliates', 'filters'));
    }

    /**
     * Sales Report (based on approved referrals)
     */
    public function sales(Request $request)
    {
        $filters = $this->getReportFilters($request);

        $query = DB::table('affiliate_referrals as ar')
            ->join('affiliates as a', 'ar.affiliate_id', '=', 'a.id')
            ->whereIn('ar.status', ['approved', 'paid']) // Only count completed sales
            ->select(
                'ar.order_id',
                'a.name as affiliate_name',
                'ar.amount as order_amount',
                'ar.commission_amount',
                'ar.created_at as sale_date'
            );

        if (!empty($filters['selected_affiliates'])) {
            $query->whereIn('ar.affiliate_id', $filters['selected_affiliates']);
        }
        if ($filters['from_date']) {
            $query->where('ar.created_at', '>=', $filters['from_date']);
        }
        if ($filters['to_date']) {
            $query->where('ar.created_at', '<=', $filters['to_date']);
        }

        $sales = $query->orderBy('ar.created_at', 'desc')->paginate(20);

        return view('admin.reports.sales', compact('sales', 'filters'));
    }

    /**
     * Payouts Report
     */
    public function payouts(Request $request)
    {
        $filters = $this->getReportFilters($request);

        $query = DB::table('affiliate_payouts as ap')
            ->join('affiliates as a', 'ap.affiliate_id', '=', 'a.id')
            ->select(
                'ap.id as payout_id',
                'a.name as affiliate_name',
                'ap.amount',
                'ap.method as payment_method',
                'ap.status',
                'ap.paid_at'
            );

        if (!empty($filters['selected_affiliates'])) {
            $query->whereIn('ap.affiliate_id', $filters['selected_affiliates']);
        }
        if ($filters['from_date']) {
            $query->where('ap.created_at', '>=', $filters['from_date']);
        }
        if ($filters['to_date']) {
            $query->where('ap.created_at', '<=', $filters['to_date']);
        }

        $payouts = $query->orderBy('ap.created_at', 'desc')->paginate(20);

        return view('admin.reports.payouts', compact('payouts', 'filters'));
    }

    /**
     * Visits Report
     */
    public function visits(Request $request)
    {
        $filters = $this->getReportFilters($request);

        $query = DB::table('affiliate_visits as av')
            ->join('affiliates as a', 'av.affiliate_id', '=', 'a.id')
            ->select(
                'a.name as affiliate_name',
                'av.ip_address',
                'av.referrer_url',
                'av.landing_url',
                'av.campaign',
                'av.created_at as visit_time'
            );

        if (!empty($filters['selected_affiliates'])) {
            $query->whereIn('av.affiliate_id', $filters['selected_affiliates']);
        }
        if ($filters['from_date']) {
            $query->where('av.created_at', '>=', $filters['from_date']);
        }
        if ($filters['to_date']) {
            $query->where('av.created_at', '<=', $filters['to_date']);
        }

        $visits = $query->orderBy('av.created_at', 'desc')->paginate(20);

        return view('admin.reports.visits', compact('visits', 'filters'));
    }

    /**
     * Campaigns Report
     */
    public function campaigns(Request $request)
    {
        $filters = $this->getReportFilters($request);

        $query = DB::table('affiliate_visits as av')
            ->leftJoin('affiliate_referrals as ar', 'av.id', '=', 'ar.visit_id')
            ->whereNotNull('av.campaign')
            ->select(
                'av.campaign',
                DB::raw('COUNT(DISTINCT av.id) as total_visits'),
                DB::raw("COUNT(DISTINCT CASE WHEN ar.status IN ('approved', 'paid') THEN ar.id ELSE NULL END) as total_conversions"),
                DB::raw("SUM(CASE WHEN ar.status IN ('approved', 'paid') THEN ar.commission_amount ELSE 0 END) as total_commission")
            )
            ->groupBy('av.campaign');

        if ($filters['from_date']) {
            $query->where('av.created_at', '>=', $filters['from_date']);
        }
        if ($filters['to_date']) {
            $query->where('av.created_at', '<=', $filters['to_date']);
        }

        $campaigns = $query->orderBy('av.campaign')->paginate(20);

        return view('admin.reports.campaigns', compact('campaigns', 'filters'));
    }
}