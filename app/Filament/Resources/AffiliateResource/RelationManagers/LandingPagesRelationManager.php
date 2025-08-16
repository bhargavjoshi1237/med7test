<?php

namespace App\Filament\Resources\AffiliateResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;

class LandingPagesRelationManager extends RelationManager
{
    protected static string $relationship = 'landingPages';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('page_url')->label('Page URL')->url()->required(),
                Forms\Components\TextInput::make('landing_page_url')->label('Landing Page URL')->url()->required(),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('page_url')
            ->columns([
                Tables\Columns\TextColumn::make('page_url'),
                Tables\Columns\TextColumn::make('landing_page_url'),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make(),
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
