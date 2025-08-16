<?php

namespace App\Filament\Resources;

use App\Filament\Resources\AffiliateGroupResource\Pages;
use App\Models\AffiliateGroup;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class AffiliateGroupResource extends Resource
{
    protected static ?string $model = AffiliateGroup::class;

    protected static ?string $navigationIcon = 'heroicon-o-tag';
    protected static ?string $navigationGroup = 'Affiliates';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('name')->required()->unique(ignoreRecord: true),
                Forms\Components\Select::make('rate_type')
                    ->label('Commission Type')
                    ->options([
                        'percentage' => 'Percentage',
                        'flat' => 'Flat Rate',
                    ])
                    ->reactive()
                    ->afterStateUpdated(fn ($state, callable $set) => $state ? null : $set('rate', null)),
                Forms\Components\TextInput::make('rate')
                    ->label('Commission Rate')
                    ->numeric()
                    ->step(0.01)
                    ->suffix(fn ($get) => $get('rate_type') === 'percentage' ? '%' : '$')
                    ->visible(fn ($get) => filled($get('rate_type')))
                    ->helperText(fn ($get) => match($get('rate_type')) {
                        'percentage' => 'Enter percentage (e.g., 10 for 10%)',
                        'flat' => 'Enter flat amount in dollars',
                        default => null
                    }),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')->searchable(),
                Tables\Columns\TextColumn::make('rate_type')
                    ->label('Commission Type')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'percentage' => 'success',
                        'flat' => 'info',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'percentage' => 'Percentage',
                        'flat' => 'Flat Rate',
                        default => 'Not Set',
                    }),
                Tables\Columns\TextColumn::make('rate')
                    ->label('Rate')
                    ->formatStateUsing(fn ($state, $record) => 
                        $state ? ($record->rate_type === 'percentage' ? $state . '%' : '$' . $state) : 'Not Set'
                    ),
                Tables\Columns\TextColumn::make('affiliates_count')->counts('affiliates'),
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
            'index' => Pages\ListAffiliateGroups::route('/'),
            'create' => Pages\CreateAffiliateGroup::route('/create'),
            'edit' => Pages\EditAffiliateGroup::route('/{record}/edit'),
        ];
    }
}
