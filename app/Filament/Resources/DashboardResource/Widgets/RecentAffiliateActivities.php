<?php

namespace App\Filament\Resources\DashboardResource\Widgets;

use App\Models\AffiliateActivity;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;

class RecentAffiliateActivities extends BaseWidget
{
    protected static ?string $heading = 'Recent Affiliate Activities';

    protected int | string | array $columnSpan = 'full';

    public function table(Table $table): Table
    {
        return $table
            ->query(
                AffiliateActivity::query()
                    ->with(['affiliate'])
                    ->latest('activity_date')
                    ->limit(10)
            )
            ->columns([
                Tables\Columns\TextColumn::make('affiliate.name')
                    ->label('Affiliate')
                    ->searchable()
                    ->sortable(),
                
                Tables\Columns\TextColumn::make('order_reference')
                    ->label('Order Reference')
                    ->searchable()
                    ->default('N/A'),
                
                Tables\Columns\TextColumn::make('commission_amount')
                    ->label('Commission')
                    ->money('USD')
                    ->sortable(),
                
                Tables\Columns\TextColumn::make('commission_type')
                    ->label('Commission Type')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'flat' => 'success',
                        'percentage' => 'info',
                        default => 'gray',
                    }),
                
                Tables\Columns\TextColumn::make('activity_date')
                    ->label('Date')
                    ->dateTime()
                    ->sortable(),
            ])
            ->actions([
                // Actions can be added here when AffiliateActivity resource is created
            ]);
    }
}