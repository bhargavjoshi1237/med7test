<?php

namespace App\Filament\Resources\AffiliateResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;

class MinimumThresholdRelationManager extends RelationManager
{
    protected static string $relationship = 'minimumThreshold';
    protected static ?string $title = 'Minimum Payout Threshold';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('minimum_threshold')
                    ->label('Minimum Threshold')
                    ->numeric()
                    ->step(0.01)
                    ->prefix('$')
                    ->required(),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('minimum_threshold')
            ->columns([
                Tables\Columns\TextColumn::make('minimum_threshold')
                    ->label('Minimum Threshold')
                    ->money('usd'),
                Tables\Columns\TextColumn::make('updated_at')
                    ->label('Last Updated')
                    ->dateTime(),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make()
                    ->visible(fn () => !$this->getOwnerRecord()->minimumThreshold),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }
}