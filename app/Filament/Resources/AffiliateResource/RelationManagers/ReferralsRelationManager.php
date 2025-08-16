<?php

namespace App\Filament\Resources\AffiliateResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;

class ReferralsRelationManager extends RelationManager
{
    protected static string $relationship = 'referrals';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                // Referrals are usually created automatically, so form is simple
                Forms\Components\Select::make('status')->options(['pending' => 'Pending', 'unpaid' => 'Unpaid', 'paid' => 'Paid', 'rejected' => 'Rejected'])->required(),
                Forms\Components\TextInput::make('description'),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('description')
            ->columns([
                Tables\Columns\TextColumn::make('id')->sortable(),
                Tables\Columns\TextColumn::make('amount')->money(fn ($record) => $record->currency ?? 'USD'),
                Tables\Columns\TextColumn::make('commission_amount')->money(fn ($record) => $record->currency ?? 'USD'),
                Tables\Columns\TextColumn::make('status')->badge(),
                Tables\Columns\TextColumn::make('created_at')->dateTime()->sortable(),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                // Usually no create action
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    //
                ]),
            ]);
    }
}
