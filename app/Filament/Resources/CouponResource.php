<?php

namespace App\Filament\Resources;

use App\Filament\Resources\CouponResource\Pages;
use App\Models\Coupon;
use Lunar\Models\Discount;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class CouponResource extends Resource
{
    protected static ?string $model = Coupon::class;

    protected static ?string $navigationIcon = 'heroicon-o-ticket';
    protected static ?string $navigationGroup = 'Affiliates';
    protected static ?string $navigationLabel = 'Affiliate Coupons';
    // protected static ?int $navigationSort = 4;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('code')->required()->unique(ignoreRecord: true),
                Forms\Components\Select::make('affiliate_id')->relationship('affiliate', 'name')->searchable(),
                Forms\Components\Select::make('status')->options(['active' => 'Active', 'inactive' => 'Inactive', 'expired' => 'Expired'])->searchable(),
                Forms\Components\DatePicker::make('starts_at'),
                Forms\Components\DatePicker::make('expires_at'),
                Forms\Components\Select::make('type')->options(['percentage' => 'Percentage', 'flat' => 'Flat'])->required(),
                Forms\Components\TextInput::make('amount')->numeric()->required(),
                Forms\Components\TextInput::make('name')->required(),

                // Only show Discount Integration section on create
                ...($form->getOperation() === 'create' ? [
                    Forms\Components\Section::make('Discount Integration')
                        ->schema([
                            Forms\Components\Checkbox::make('create_discount')
                                ->label('Create Discount Which Applicable On Checkout')
                                ->helperText('Checking this box will automaticaly create a dicount coupon which can be used on the checkout to actuly apply discount, to update and assign discount checout the discount section in the Sales Section.')
                                ->reactive(),
                            Forms\Components\Group::make([
                                Forms\Components\TextInput::make('discount_handle')
                                    ->label('Discount Handle')
                                    ->helperText('Unique identifier for the discount')
                                    ->required()
                                    ->visible(fn (Forms\Get $get) => $get('create_discount')),
                                Forms\Components\Select::make('discount_type')
                                    ->label('Discount Type')
                                    ->options([
                                        'Lunar\DiscountTypes\AmountOff' => 'AmountOff',
                                        'Lunar\DiscountTypes\BuyXGetY' => 'BuyXGetY',
                                    ])
                                    ->default('coupon')
                                    ->required()
                                    ->visible(fn (Forms\Get $get) => $get('create_discount')),
                            ])->columns(2),
                        ])
                        ->collapsible()
                        ->collapsed(),
                ] : []),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('code')->searchable(),
                Tables\Columns\TextColumn::make('affiliate.name')->searchable(),
                Tables\Columns\TextColumn::make('type'),
                Tables\Columns\TextColumn::make('amount'),
                Tables\Columns\IconColumn::make('has_discount')
                    ->label('Discount')
                    ->boolean()
                    ->getStateUsing(function ($record) {
                        return Discount::where('coupon', $record->code)->exists();
                    })
                    ->tooltip('Has associated LunarPHP discount'),
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
            'index' => Pages\ListCoupons::route('/'),
            'create' => Pages\CreateCoupon::route('/create'),
            'edit' => Pages\EditCoupon::route('/{record}/edit'),
        ];
    }
}
        