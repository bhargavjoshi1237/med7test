<?php

namespace App\Console\Commands;

use App\Models\Affiliate;
use App\Services\AffiliateCommissionService;
use Illuminate\Console\Command;

class CalculateAffiliateCommissions extends Command
{
    protected $signature = 'affiliate:calculate-commissions {affiliate_id?}';
    protected $description = 'Calculate pending commissions for affiliates';

    public function handle(AffiliateCommissionService $commissionService): int
    {
        $affiliateId = $this->argument('affiliate_id');
        
        if ($affiliateId) {
            $affiliate = Affiliate::find($affiliateId);
            if (!$affiliate) {
                $this->error("Affiliate with ID {$affiliateId} not found.");
                return 1;
            }
            
            $this->calculateForAffiliate($affiliate, $commissionService);
        } else {
            $affiliates = Affiliate::where('status', 'active')->get();
            
            if ($affiliates->isEmpty()) {
                $this->info('No active affiliates found.');
                return 0;
            }
            
            foreach ($affiliates as $affiliate) {
                $this->calculateForAffiliate($affiliate, $commissionService);
            }
        }
        
        return 0;
    }
    
    private function calculateForAffiliate(Affiliate $affiliate, AffiliateCommissionService $commissionService): void
    {
        $this->info("Calculating commissions for: {$affiliate->name}");
        
        $pendingCommission = $commissionService->getPendingCommission($affiliate->id);
        
        $this->table(
            ['Metric', 'Value'],
            [
                ['Period From', $pendingCommission['from_date']->format('M d, Y')],
                ['Period To', $pendingCommission['to_date']->format('M d, Y')],
                ['Activities Count', $pendingCommission['activities_count']],
                ['Total Commission', '$' . number_format($pendingCommission['total_commission'], 2)],
                ['Currency', $pendingCommission['currency']?->code ?? 'Not Set'],
                ['Minimum Threshold', '$' . number_format($pendingCommission['minimum_threshold'], 2)],
                ['Meets Threshold', $pendingCommission['meets_threshold'] ? '✅ Yes' : '❌ No'],
                ['Last Payout', $pendingCommission['last_payout'] ? 
                    $pendingCommission['last_payout']->paid_at->format('M d, Y') . ' ($' . 
                    number_format($pendingCommission['last_payout']->amount, 2) . ')' : 'None'],
            ]
        );
        
        $this->newLine();
    }
}