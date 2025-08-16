<?php

namespace App\Filament\Pages;

use App\Models\Affiliate;
use App\Models\AffiliateActivity;
use App\Models\AffiliatePayout;
use Carbon\Carbon;
use Filament\Pages\Page;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;

use Filament\Tables\Actions\ViewAction;

class AffiliateDashboard extends Page
{

    protected static ?string $navigationIcon = 'heroicon-o-chart-bar';
    protected static ?string $navigationGroup = 'Affiliates';
    protected static ?string $navigationLabel = 'Dashboard';
    protected static ?int $navigationSort = 1;
    protected static string $view = 'filament.pages.affiliate-dashboard';  
    protected static ?string $title = 'Affiliate Dashboard';
    protected static ?string $slug = 'affiliate/dashboard';

    public array $stats = []; // New property to store stats

    public function mount(): void
    {
        $this->stats = [
            'total_affiliates' => Affiliate::count(),
            'active_affiliates' => Affiliate::where('status', 'active')->count(),
            'total_commissions_earned' => AffiliateActivity::sum('commission_amount'),
            'pending_payouts' => AffiliatePayout::where('status', 'pending')->sum('amount'),
        ];
    }

    protected function getColumns(): int | string | array
    {
        return [
            'md' => 2,
            'xl' => 3,
        ];
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(AffiliateActivity::query())
            ->columns([
                TextColumn::make('affiliate.name')
                    ->label('Affiliate')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('activity_type')
                    ->label('Activity Type')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('commission_amount')
                    ->label('Commission Amount')
                    ->money('usd')
                    ->sortable(),
                TextColumn::make('description')
                    ->label('Description')
                    ->wrap()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('created_at')
                    ->label('Date')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(),
            ])
            ->filters([
                SelectFilter::make('activity_type')
                    ->options([
                        'sale' => 'Sale',
                        'referral' => 'Referral',
                        'payout' => 'Payout',
                        'adjustment' => 'Adjustment',
                    ])
                    ->label('Activity Type'),
            ])
            ->actions([
                // You can add actions here if needed, e.g., ViewAction::make()
            ])
            ->bulkActions([
                // You can add bulk actions here if needed
            ])
            ->defaultSort('created_at', 'desc');
    }
}