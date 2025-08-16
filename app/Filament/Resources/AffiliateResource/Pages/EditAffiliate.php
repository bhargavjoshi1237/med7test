<?php

namespace App\Filament\Resources\AffiliateResource\Pages;

use App\Filament\Resources\AffiliateResource;
use App\Models\AffiliateMinimumThreshold;
use App\Models\AffiliateVisit; // Import AffiliateVisit model
use App\Models\AffiliateActivity; // Import AffiliateActivity model
use Filament\Actions;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Pages\EditRecord;

class EditAffiliate extends EditRecord
{
    protected static string $resource = AffiliateResource::class;

    public function form(Form $form): Form
    {
        // Access the current record
        $record = $this->record;

        // Initialize stats variables
        $totalVisitsLastMonth = 0;
        $totalPurchasesLastMonth = 0;
        $usersReferredLastMonth = 0;

        // Only calculate stats if a record exists (i.e., not creating a new one)
        if ($record) {
            // Calculate Total Visits for the last month
            $totalVisitsLastMonth = AffiliateVisit::where('affiliate_id', $record->id)
                ->where('created_at', '>=', now()->subMonth())
                ->count();

            // Calculate Total Purchases for the last month
            $totalPurchasesLastMonth = AffiliateActivity::where('affiliate_id', $record->id)    // Assuming order_reference indicates a purchase
                ->where('activity_date', '>=', now()->subMonth())
                ->count();

            // Calculate Users Referred for the last month
            $usersReferredLastMonth = \App\Models\Affiliate::where('parent_id', $record->id)
                ->where('created_at', '>=', now()->subMonth())
                ->count();
        }

        return $form
            ->schema([
                Forms\Components\Section::make('Stats Legend (Last Month)')
                    ->schema([
                        Forms\Components\Placeholder::make('total_visits_last_month')
                            ->label('Total Visits')
                            ->content($totalVisitsLastMonth),
                        Forms\Components\Placeholder::make('total_purchases_last_month')
                            ->label('Total Purchases')
                            ->content($totalPurchasesLastMonth),
                        Forms\Components\Placeholder::make('users_referred_last_month')
                            ->label('Users Referred')
                            ->content($usersReferredLastMonth),
                    ])->columns(3),
                Forms\Components\Section::make('Account Details')
                    ->schema([
                        Forms\Components\Select::make('user_id')->relationship('user', 'name')->searchable()->helperText('Optional: Link this affiliate to a user account.'),
                        Forms\Components\TextInput::make('name')->required(),
                        Forms\Components\TextInput::make('email')->email()->required()->unique(ignoreRecord: true),
                        Forms\Components\Select::make('status')->options(['pending' => 'Pending', 'active' => 'Active', 'inactive' => 'Inactive', 'rejected' => 'Rejected'])->default('pending')->required(),
                        Forms\Components\Select::make('groups')->multiple()->relationship('groups', 'name'),
                    ])->columns(2),
                Forms\Components\Section::make('Commission Settings')
                    ->schema([
                        Forms\Components\TextInput::make('rate')->numeric()->helperText('Default commission rate.'),
                        Forms\Components\Select::make('rate_type')->options(['percentage' => 'Percentage', 'flat' => 'Flat']),
                        Forms\Components\Select::make('tiered_rate_id')->relationship('tieredRate', 'name')->helperText('Apply a tiered rate structure.'),
                        Forms\Components\TextInput::make('signup_bonus')->numeric()->default(0),
                    ])->columns(2),
                Forms\Components\Section::make('Payment Settings')
                    ->schema([
                        Forms\Components\TextInput::make('payment_email')->email()->label('Payment Email (e.g., PayPal)'),
                        Forms\Components\TextInput::make('store_credit_balance')->numeric(),
                        Forms\Components\Select::make('currency_id')
                            ->label('Commission Currency')
                            ->relationship('currency', 'name')
                            ->searchable()
                            ->preload()
                            ->helperText('Currency for commission calculations'),
                        Forms\Components\TextInput::make('minimum_threshold')
                            ->label('Minimum Payout Threshold')
                            ->numeric()
                            ->step(0.01)
                            ->prefix('$')
                            ->helperText('Minimum amount required before payout can be processed')
                            ->afterStateHydrated(function (Forms\Components\TextInput $component, $record) {
                                if ($record && $record->minimumThreshold) {
                                    $component->state($record->minimumThreshold->minimum_threshold);
                                }
                            }),
                    ])->columns(2),
                Forms\Components\Section::make('Advanced Settings')
                    ->schema([
                        Forms\Components\Select::make('parent_id')->relationship('parent', 'name')->searchable()->label('Parent Affiliate (for MLM)'),
                        Forms\Components\TextInput::make('slug')->unique(ignoreRecord: true)->label('Custom Affiliate Slug'),
                        // Forms\Components\TextInput::make('website_url')->url()->label('Website URL (for Direct Link Tracking)'),
                        // Forms\Components\TextInput::make('cookie_duration')->numeric()->helperText('Custom cookie expiration in days. Leave blank for global setting.'),
                    
                        ])->columns(2),
            ]);
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        // Remove minimum_threshold from the main data array
        $minimumThreshold = $data['minimum_threshold'] ?? null;
        unset($data['minimum_threshold']);
        
        // Store it for later processing
        $this->minimumThresholdValue = $minimumThreshold;
        
        return $data;
    }

    protected function afterSave(): void
    {
        // Handle minimum threshold separately
        if (isset($this->minimumThresholdValue)) {
            if ($this->minimumThresholdValue > 0) {
                AffiliateMinimumThreshold::updateOrCreate(
                    ['affiliate_id' => $this->record->id],
                    ['minimum_threshold' => $this->minimumThresholdValue]
                );
            } else {
                // Delete threshold if set to 0
                AffiliateMinimumThreshold::where('affiliate_id', $this->record->id)->delete();
            }
        }
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
