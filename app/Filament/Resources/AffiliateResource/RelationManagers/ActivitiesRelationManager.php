<?php

namespace App\Filament\Resources\AffiliateResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class ActivitiesRelationManager extends RelationManager
{
    protected static string $relationship = 'activities';
    protected static ?string $title = 'Purchase Activities';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                // Activities are created automatically, no manual creation
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('activity_type')
            ->columns([
                
                Tables\Columns\TextColumn::make('buyer.name')
                    ->label('Buyer')
                    ->placeholder('Guest'),
                Tables\Columns\TextColumn::make('productVariant.sku')
                    ->label('Product')
                    ->limit(30)
                    ->placeholder('N/A'),
                Tables\Columns\TextColumn::make('product_price')
                    ->label('Product Price')
                    ->money('usd')
                    ->placeholder('$0.00'),
                Tables\Columns\TextColumn::make('commission_rate')
                    ->label('Rate')
                    ->suffix('%')
                    ->placeholder('0%'),
                Tables\Columns\TextColumn::make('commission_amount')
                    ->label('Commission')
                    ->money('usd')
                    ->color('success')
                    ->weight('bold'),
                Tables\Columns\TextColumn::make('activity_date')
                    ->label('Date')
                    ->dateTime()
                    ->sortable(),
            ])
            ->filters([
                // Tables\Filters\SelectFilter::make('activity_type')
                //     ->options([
                //         'purchase' => 'Purchase',
                //         'refund' => 'Refund',
                //         'visit' => 'Visit',
                //     ]),
                Tables\Filters\Filter::make('activity_date')
                    ->form([
                        Forms\Components\DatePicker::make('from')
                            ->label('From Date'),
                        Forms\Components\DatePicker::make('until')
                            ->label('Until Date'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['from'],
                                fn (Builder $query, $date): Builder => $query->whereDate('activity_date', '>=', $date),
                            )
                            ->when(
                                $data['until'],
                                fn (Builder $query, $date): Builder => $query->whereDate('activity_date', '<=', $date),
                            );
                    }),
            ])
            ->headerActions([
                // No create action - activities are created automatically
            ])
            ->actions([
                // Tables\Actions\ViewAction::make(),
            ])
            ->bulkActions([
                // No bulk actions for activities
            ])
            ->defaultSort('activity_date', 'desc');
    }
}