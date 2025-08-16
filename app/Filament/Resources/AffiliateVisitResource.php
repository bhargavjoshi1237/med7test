<?php

namespace App\Filament\Resources;

use App\Filament\Resources\AffiliateVisitResource\Pages;
use App\Models\AffiliateVisit;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class AffiliateVisitResource extends Resource
{
    protected static ?string $model = AffiliateVisit::class;

    protected static ?string $navigationIcon = 'heroicon-o-cursor-arrow-rays';
    protected static ?string $navigationGroup = 'Affiliates';
    protected static ?string $modelLabel = 'Visit';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                // Visits are not manually created
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('affiliate.name')->searchable()->sortable(),
                Tables\Columns\TextColumn::make('ip')->searchable(),
                Tables\Columns\TextColumn::make('url')->limit(50),
                Tables\Columns\IconColumn::make('converted')->boolean(),
                Tables\Columns\TextColumn::make('created_at')->dateTime()->sortable(),
            ])
            ->filters([
                Tables\Filters\TernaryFilter::make('converted'),
                Tables\Filters\SelectFilter::make('affiliate')
                    ->relationship('affiliate', 'name')
                    ->searchable()
                    ->preload(),
            ])
            ->actions([
                // No actions
            ])
            ->bulkActions([
                // No bulk actions
            ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListAffiliateVisits::route('/'),
        ];
    }
}
