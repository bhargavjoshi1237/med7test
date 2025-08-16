<?php

namespace App\Filament\Resources;

use App\Filament\Resources\TieredRateResource\Pages;
use App\Models\TieredRate;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class TieredRateResource extends Resource
{
    protected static ?string $model = TieredRate::class;

    protected static ?string $navigationIcon = 'heroicon-o-chart-bar-square';
    protected static ?string $navigationGroup = 'Affiliates';
    protected static ?string $modelLabel = 'Tiered Rate Structure';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('name')->required(),
                Forms\Components\Select::make('type')->options(['referrals' => 'Number of Referrals', 'earnings' => 'Total Earnings'])->required(),
                Forms\Components\KeyValue::make('tiers')
                    ->keyLabel('Threshold')
                    ->valueLabel('Rate (%)')
                    ->helperText('Example: Key=10, Value=25 means a 25% rate for up to 10 referrals/earnings.')
                    ->required(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')->searchable(),
                Tables\Columns\TextColumn::make('type'),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
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
            'index' => Pages\ListTieredRates::route('/'),
            'create' => Pages\CreateTieredRate::route('/create'),
            'edit' => Pages\EditTieredRate::route('/{record}/edit'),
        ];
    }
}
