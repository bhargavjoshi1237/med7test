<?php

// INFO: Make sure to register this widget in DashboardResource::getWidgets(),
// and then again in getHeaderWidgets() or getFooterWidgets() of any DashboardResource page.

namespace App\Filament\Resources\DashboardResource\Widgets;

use Filament\Tables;
use App\Models\Affiliate;
use App\Models\AffiliateActivity;
use Carbon\Carbon;
 
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;

class TopAffiliatesWidget extends BaseWidget
{   
    protected static ?string $heading = 'Top Performing Affiliates (This Month)';
    protected static ?int $sort = 5;
    protected int | string | array $columnSpan = 'full';

    public function table(Table $table): Table
    {
          $thisMonth = Carbon::now()->startOfMonth();
        
        return $table
            ->query(
                Affiliate::query()
                    ->select('affiliates.*')
                    ->selectRaw('COALESCE(SUM(affiliate_activity.commission_amount), 0) as total_commission')
                    ->selectRaw('COUNT(affiliate_activity.id) as total_activities')
                    ->leftJoin('affiliate_activity', function ($join) use ($thisMonth) {
                        $join->on('affiliates.id', '=', 'affiliate_activity.affiliate_id')
                             ->where('affiliate_activity.activity_date', '>=', $thisMonth);
                    })
                    ->groupBy('affiliates.id')
                    ->orderByDesc('total_commission')
                    ->limit(10)
            )
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('Affiliate Name')
                    ->searchable()
                    ->sortable(),
                
                Tables\Columns\TextColumn::make('email')
                    ->label('Email')
                    ->searchable(),
                
                Tables\Columns\BadgeColumn::make('status')
                    ->colors([
                        'success' => 'active',
                        'warning' => 'pending',
                        'danger' => 'rejected',
                        'secondary' => 'inactive',
                    ]),
                
                Tables\Columns\TextColumn::make('total_activities')
                    ->label('Activities')
                    ->alignCenter()
                    ->sortable(),
                
                Tables\Columns\TextColumn::make('total_commission')
                    ->label('Commission')
                    ->money('usd')
                    ->sortable()
                    ->alignEnd(),
                
                Tables\Columns\TextColumn::make('rate')
                    ->label('Rate')
                    ->formatStateUsing(fn ($state, $record) => 
                        $state ? ($record->rate_type === 'percentage' ? $state . '%' : '$' . $state) : 'Not Set'
                    )
                    ->alignCenter(),
            ])
            ->defaultSort('total_commission', 'desc');
    }
}
