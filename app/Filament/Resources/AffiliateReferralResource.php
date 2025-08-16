<?php

namespace App\Filament\Resources;

use App\Filament\Resources\AffiliateReferralResource\Pages;
use App\Models\AffiliateReferral;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class AffiliateReferralResource extends Resource
{
    protected static ?string $model = AffiliateReferral::class;

    protected static ?string $navigationIcon = 'heroicon-o-currency-dollar';
    protected static ?string $navigationGroup = 'Affiliates';
    protected static ?string $modelLabel = 'Referral';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('affiliate_id')->relationship('affiliate', 'name')->required(),
                Forms\Components\TextInput::make('amount')->numeric()->required(),
                Forms\Components\TextInput::make('commission_amount')->numeric()->required(),
                Forms\Components\TextInput::make('commission_rate')->numeric()->required(),
Forms\Components\Select::make('commission_type')->options(['percentage' => 'Percentage', 'flat' => 'Flat', 'test' => 'Test', 'bonus' => 'Bonus', 'custom' => 'Custom'])->required(),
                Forms\Components\TextInput::make('currency')->required()->default('USD'),
                Forms\Components\Select::make('status')->options(['pending' => 'Pending', 'unpaid' => 'Unpaid', 'paid' => 'Paid', 'rejected' => 'Rejected'])->required(),
                Forms\Components\Textarea::make('description')->columnSpanFull(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('affiliate.name')->searchable()->sortable(),
                Tables\Columns\TextColumn::make('amount')->money(fn ($record) => $record->currency ?? 'USD')->sortable(),
                Tables\Columns\TextColumn::make('commission_amount')->money(fn ($record) => $record->currency ?? 'USD')->sortable(),
                Tables\Columns\TextColumn::make('status')->badge()->sortable()
                    ->color(fn (string $state): string => match ($state) {
                        'pending' => 'warning',
                        'unpaid' => 'gray',
                        'paid' => 'success',
                        'rejected' => 'danger',
                    }),
                Tables\Columns\TextColumn::make('created_at')->dateTime()->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'pending' => 'Pending',
                        'unpaid' => 'Unpaid',
                        'paid' => 'Paid',
                        'rejected' => 'Rejected',
                    ]),
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
            'index' => Pages\ListAffiliateReferrals::route('/'),
            'create' => Pages\CreateAffiliateReferral::route('/create'),
            'edit' => Pages\EditAffiliateReferral::route('/{record}/edit'),
        ];
    }
}
