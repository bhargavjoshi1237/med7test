<?php

namespace App\Filament\Resources;

use App\Filament\Resources\AffiliateCreativeResource\Pages;
use App\Models\AffiliateCreative;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class AffiliateCreativeResource extends Resource
{
    protected static ?string $model = AffiliateCreative::class;

    protected static ?string $navigationIcon = 'heroicon-o-photo';
    protected static ?string $navigationGroup = 'Affiliates';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('name')->required(),
                Forms\Components\Select::make('category_id')->relationship('category', 'name'),
                Forms\Components\Textarea::make('description')->columnSpanFull(),
                Forms\Components\TextInput::make('url')->url()->required(),
                Forms\Components\TextInput::make('text'),
                Forms\Components\FileUpload::make('image')->image(),
                Forms\Components\Select::make('type')->options(['link' => 'Link', 'banner' => 'Banner'])->default('link')->required(),
                Forms\Components\Select::make('status')->options(['active' => 'Active', 'inactive' => 'Inactive'])->default('active')->required(),
                Forms\Components\DateTimePicker::make('starts_at'),
                Forms\Components\DateTimePicker::make('expires_at'),
                Forms\Components\Select::make('affiliates')->multiple()->relationship('affiliates', 'name')->helperText('Assign to specific affiliates to make this a private creative.'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')->searchable(),
                Tables\Columns\TextColumn::make('type'),
                Tables\Columns\IconColumn::make('status')->icon(fn (string $state): string => match ($state) {
                    'active' => 'heroicon-o-check-circle',
                    'inactive' => 'heroicon-o-x-circle',
                })->color(fn (string $state): string => match ($state) {
                    'active' => 'success',
                    'inactive' => 'danger',
                }),
                Tables\Columns\TextColumn::make('created_at')->dateTime()->sortable()->toggleable(isToggledHiddenByDefault: true),
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
            'index' => Pages\ListAffiliateCreatives::route('/'),
            'create' => Pages\CreateAffiliateCreative::route('/create'),
            'edit' => Pages\EditAffiliateCreative::route('/{record}/edit'),
        ];
    }
}
