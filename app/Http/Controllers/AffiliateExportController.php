<?php

namespace App\Http\Controllers;

use App\Models\Affiliate;
use App\Models\AffiliateActivity;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Http\Request;

class AffiliateExportController extends Controller
{
    public function exportActivityReport(Request $request)
    {
        $request->validate([
            'affiliate_id' => 'required',
            'duration' => 'required|in:last_month,last_week,custom',
            'start_date' => 'required_if:duration,custom|date',
            'end_date' => 'required_if:duration,custom|date|after:start_date',
        ]);

        // Calculate date range
        $dateRange = $this->getDateRange(
            $request->duration,
            $request->start_date,
            $request->end_date
        );

        // Get activities based on affiliate selection
        $query = AffiliateActivity::with(['affiliate', 'productVariant.product', 'buyer'])
            ->whereBetween('activity_date', [$dateRange['start'], $dateRange['end']])
            ->orderBy('activity_date', 'desc');

        if ($request->affiliate_id !== 'all') {
            $query->where('affiliate_id', $request->affiliate_id);
        }

        $activities = $query->get();

        if ($activities->isEmpty()) {
            return response()->json(['error' => 'No activities found for the selected criteria.'], 404);
        }

        // Generate PDF
        $pdf = Pdf::loadView('exports.affiliate-activity-report', [
            'activities' => $activities,
            'dateRange' => $dateRange,
            'affiliateName' => $request->affiliate_id === 'all' 
                ? 'All Affiliates' 
                : Affiliate::find($request->affiliate_id)->name,
            'totalCommission' => $activities->sum('commission_amount'),
            'totalActivities' => $activities->count(),
        ]);

        $filename = 'affiliate-activity-report-' . 
            $dateRange['start']->format('Y-m-d') . '-to-' . 
            $dateRange['end']->format('Y-m-d') . '.pdf';

        return $pdf->download($filename);
    }

    protected function getDateRange(string $duration, ?string $startDate = null, ?string $endDate = null): array
    {
        $end = Carbon::now();

        switch ($duration) {
            case 'last_week':
                $start = Carbon::now()->subWeek();
                break;
            case 'last_month':
                $start = Carbon::now()->subMonth();
                break;
            case 'custom':
                $start = Carbon::parse($startDate);
                $end = Carbon::parse($endDate);
                break;
            default:
                $start = Carbon::now()->subMonth();
                break;
        }

        return [
            'start' => $start,
            'end' => $end,
        ];
    }
}