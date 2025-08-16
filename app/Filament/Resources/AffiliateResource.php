<?php

namespace App\Filament\Resources;

use App\Filament\Resources\AffiliateResource\Pages;
use App\Filament\Resources\AffiliateResource\RelationManagers;
use App\Models\Affiliate;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
    
class AffiliateResource extends Resource
{
    protected static ?string $model = Affiliate::class;

    protected static ?string $navigationIcon = 'heroicon-o-user-group';
    protected static ?string $navigationGroup = 'Affiliates';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Account Details')
                    ->schema([
                        Forms\Components\Select::make('user_id')->relationship('user', 'name')->searchable()->helperText('Optional: Link this affiliate to a user account.'),
                        Forms\Components\TextInput::make('name')->required(),
                        Forms\Components\TextInput::make('email')->email()->required()->unique(ignoreRecord: true),
                        Forms\Components\Select::make('status')->options(['pending' => 'Pending', 'active' => 'Active', 'inactive' => 'Inactive', 'rejected' => 'Rejected'])->default('pending')->required(),
                        Forms\Components\Select::make('groups')->multiple()->relationship('groups', 'name'),
                    ])->columns(2),
                Forms\Components\Section::make('Commission Settings')
                    ->schema([
                        Forms\Components\TextInput::make('rate')->numeric()->helperText('Default commission rate.'),
                        Forms\Components\Select::make('rate_type')->options(['percentage' => 'Percentage', 'flat' => 'Flat']),
                        Forms\Components\Select::make('tiered_rate_id')->relationship('tieredRate', 'name')->helperText('Apply a tiered rate structure.'),
                        Forms\Components\TextInput::make('signup_bonus')->numeric()->default(0),
                    ])->columns(2),
                Forms\Components\Section::make('Payment Settings')
                    ->schema([
                        Forms\Components\TextInput::make('payment_email')->email()->label('Payment Email (e.g., PayPal)'),
                        Forms\Components\TextInput::make('store_credit_balance')->numeric(),
                        Forms\Components\Select::make('currency_id')
                            ->label('Commission Currency')
                            ->relationship('currency', 'name')
                            ->searchable()
                            ->preload()
                            ->helperText('Currency for commission calculations'),

                    ])->columns(2),
                Forms\Components\Section::make('Advanced Settings')
                    ->schema([
                        Forms\Components\Select::make('parent_id')->relationship('parent', 'name')->searchable()->label('Parent Affiliate (for MLM)'),
                        Forms\Components\TextInput::make('slug')->unique(ignoreRecord: true)->label('Custom Affiliate Slug'),
                        // Forms\Components\TextInput::make('website_url')->url()->label('Website URL (for Direct Link Tracking)'),
                        // Forms\Components\TextInput::make('cookie_duration')->numeric()->helperText('Custom cookie expiration in days. Leave blank for global setting.'),
                    
                        ])->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')->searchable()->sortable(),
                Tables\Columns\TextColumn::make('email')->searchable(),
                Tables\Columns\TextColumn::make('status')->badge()->sortable()
                    ->color(fn (string $state): string => match ($state) {
                        'pending' => 'warning',
                        'active' => 'success',
                        'inactive' => 'gray',
                        'rejected' => 'danger',
                    }),
                Tables\Columns\TextColumn::make('currency.code')
                    ->label('Currency')
                    ->badge()
                    ->placeholder('Not Set')
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('minimumThreshold.minimum_threshold')
                    ->label('Min. Threshold')
                    ->money('usd')
                    ->placeholder('$0.00')
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('children_count')->counts('children')->label('Referrals')->sortable(),
                Tables\Columns\TextColumn::make('visits_count')->counts('visits')->label('Visits')->sortable(),
                Tables\Columns\TextColumn::make('created_at')->dateTime()->sortable()->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'pending' => 'Pending',
                        'active' => 'Active',
                        'inactive' => 'Inactive',
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
            RelationManagers\ChildrenRelationManager::class,
            RelationManagers\ActivitiesRelationManager::class,
            RelationManagers\VisitsRelationManager::class,
            RelationManagers\PayoutsRelationManager::class,
            // RelationManagers\NotesRelationManager::class,
            RelationManagers\ProductRatesRelationManager::class,
            RelationManagers\CouponsRelationManager::class,
            // RelationManagers\CreativesRelationManager::class,
            // RelationManagers\LandingPagesRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListAffiliates::route('/'),
            'create' => Pages\CreateAffiliate::route('/create'),
            'edit' => Pages\EditAffiliate::route('/{record}/edit'),
        ];
    }
}
