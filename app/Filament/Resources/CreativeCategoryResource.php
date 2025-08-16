<?php

namespace App\Filament\Resources;

use App\Filament\Resources\CreativeCategoryResource\Pages;
use App\Models\CreativeCategory;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class CreativeCategoryResource extends Resource
{
    protected static ?string $model = CreativeCategory::class;

    protected static ?string $navigationIcon = 'heroicon-o-folder';
    protected static ?string $navigationGroup = 'Affiliates';
    protected static ?string $modelLabel = 'Creative Category';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('name')->required()->unique(ignoreRecord: true),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')->searchable(),
                Tables\Columns\TextColumn::make('creatives_count')->counts('creatives'),
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
            'index' => Pages\ListCreativeCategories::route('/'),
            'create' => Pages\CreateCreativeCategory::route('/create'),
            'edit' => Pages\EditCreativeCategory::route('/{record}/edit'),
        ];
    }
}
