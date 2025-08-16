<?php

namespace App\Filament\Resources\AffiliatePayoutResource\Pages;

use App\Filament\Resources\AffiliatePayoutResource;
use App\Models\Affiliate;
use App\Models\AffiliateActivity;
use App\Models\AffiliatePayout;
use Carbon\Carbon;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\CreateRecord;

class CreateAffiliatePayout extends CreateRecord
{
    protected static string $resource = AffiliatePayoutResource::class;

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('affiliate_id')
                    ->label('Affiliate')
                    ->relationship('affiliate', 'name')
                    ->searchable()
                    ->preload()
                    ->required()
                    ->reactive()
                    ->afterStateUpdated(function ($state, callable $set, callable $get) {
                        if ($state) {
                            $this->calculatePayoutInfo($state, $set, $get);
                        }
                    }),

                Forms\Components\Select::make('payout_duration')
                    ->label('Payout Duration')
                    ->options([
                        'last_paid' => 'From Last Paid Payout',
                        '1_month' => 'Last 1 Month',
                        '1_week' => 'Last 1 Week',
                        'full' => 'All Time (Full)',
                    ])
                    ->default('last_paid')
                    ->reactive()
                    ->afterStateUpdated(function ($state, callable $set, callable $get) {
                        $affiliateId = $get('affiliate_id');
                        if ($affiliateId) {
                            $this->calculatePayoutInfo($affiliateId, $set, $get);
                        }
                    })
                    ->helperText('Select the time period for commission calculation'),
                
                Forms\Components\Placeholder::make('last_payout_info')
                    ->label('Last Payout Information')
                    ->content(function (callable $get) {
                        $affiliateId = $get('affiliate_id');
                        if (!$affiliateId) return 'Select an affiliate to see payout information';
                        
                        $lastPayout = AffiliatePayout::where('affiliate_id', $affiliateId)
                            ->where('status', 'completed')
                            ->latest('paid_at')
                            ->first();
                
                        if ($lastPayout) {
                            return "Last cleared payout: {$lastPayout->paid_at->format('M d, Y')} - Amount: $" . number_format($lastPayout->amount, 2);
                        }
                        return 'No previous payouts found';
                    }),
                
                Forms\Components\Placeholder::make('minimum_threshold_info')
                    ->label('Minimum Threshold Information')
                    ->content(function (callable $get) {
                        $affiliateId = $get('affiliate_id');
                        if (!$affiliateId) return 'Select an affiliate to see threshold information';
                        $affiliate = Affiliate::find($affiliateId);
                        $threshold = $affiliate?->getMinimumThreshold() ?? 0;
                        return "Minimum Payout Threshold: $" . number_format($threshold, 2);
                    }),

                Forms\Components\Placeholder::make('commission_calculation')
                    ->label('Commission Calculation')
                    ->content(function (callable $get) {
                        $affiliateId = $get('affiliate_id');
                        $duration = $get('payout_duration') ?? 'last_paid';
                        if (!$affiliateId) return 'Select an affiliate to calculate commission';
                        $affiliate = Affiliate::find($affiliateId);
                        $dates = $this->getDateRangeForDuration($affiliateId, $duration);
                        $activities = AffiliateActivity::getActivitiesForPayout($affiliateId, $dates['from'], $dates['to'], true); // true = use >=
                        $totalCommission = AffiliateActivity::calculateTotalCommission($affiliateId, $dates['from'], $dates['to'], true); // true = use >=
                        
                        $threshold = $affiliate?->getMinimumThreshold() ?? 0;
                        $meetsThreshold = $totalCommission >= $threshold;
                        
                        $content = "Period: {$dates['from']->format('M d, Y')} to {$dates['to']->format('M d, Y')}\n";
                        $content .= "Total Activities: " . $activities->count() . "\n";
                        $content .= "Total Commission: $" . number_format($totalCommission, 2) . "\n";
                        $content .= "Minimum Threshold: $" . number_format($threshold, 2) . "\n";
                        $content .= "Meets Threshold: " . ($meetsThreshold ? '✅ Yes' : '❌ No');
                        
                        return $content;
                    }),
                
                Forms\Components\TextInput::make('amount')
                    ->label('Payout Amount')
                    ->numeric()
                    ->step(0.01)
                    ->prefix('$')
                    ->required()
                    ->helperText('This will be auto-calculated based on commission, but you can adjust if needed'),
                
                Forms\Components\Select::make('method')
                    ->label('Payout Method')
                    ->options([
                        'paypal' => 'PayPal',
                        'bank_transfer' => 'Bank Transfer',
                        'store_credit' => 'Store Credit',
                        'check' => 'Check',
                    ])
                    ->default('paypal')
                    ->required(),
                
                Forms\Components\Select::make('status')
                    ->options([
                        'pending' => 'Pending',
                        'processing' => 'Processing',
                        'completed' => 'Completed',
                        'failed' => 'Failed',
                    ])
                    ->default('pending')
                    ->required(),
                
                Forms\Components\Textarea::make('notes')
                    ->label('Notes')
                    ->rows(3)
                    ->columnSpanFull(),
            ]);
    }

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        // If status is completed, set paid_at to now
        if (($data['status'] ?? null) === 'completed') {
            $data['paid_at'] = Carbon::now();
        }
        return $data;
    }

    protected function calculatePayoutInfo($affiliateId, callable $set, callable $get): void
    {
        $duration = $get('payout_duration') ?? 'last_paid';
        $dates = $this->getDateRangeForDuration($affiliateId, $duration);
        $totalCommission = AffiliateActivity::calculateTotalCommission($affiliateId, $dates['from'], $dates['to'], true);
        
        $affiliate = Affiliate::find($affiliateId);
        $threshold = $affiliate?->getMinimumThreshold() ?? 0;
        $meetsThreshold = $totalCommission >= $threshold;
        $set('amount', $totalCommission);
        
        if ($totalCommission > 0) {
            $message = "Total commission from {$dates['from']->format('M d, Y')} to {$dates['to']->format('M d, Y')}: $" . number_format($totalCommission, 2);
            
            if (!$meetsThreshold && $threshold > 0) {
                $message .= "\n⚠️ Amount is below minimum threshold of $" . number_format($threshold, 2);
                
                Notification::make()    
                    ->title('Commission Calculated - Below Threshold')
                    ->body($message)
                    ->warning()
                    ->send();
            } else {
                Notification::make()
                    ->title('Commission Calculated')
                    ->body($message)
                    ->success()
                    ->send();
            }
        }
    }

    protected function getDateRangeForDuration($affiliateId, $duration): array
    {
        $toDate = Carbon::now();
        
        switch ($duration) {
            case '1_week':
                $fromDate = Carbon::now()->subWeek();
                break;
            case '1_month':
                $fromDate = Carbon::now()->subMonth();
                break;
            case 'full':
                $fromDate = Carbon::parse('2000-01-01');
                break;
            case 'last_paid':
            default:
                $lastPayout = AffiliatePayout::where('affiliate_id', $affiliateId)
                    ->where('status', 'completed')
                    ->latest('paid_at')
                    ->first();
                $fromDate = $lastPayout ? $lastPayout->paid_at : Carbon::parse('2000-01-01');
                break;
        }
        
        return ['from' => $fromDate, 'to' => $toDate];
    }
}
