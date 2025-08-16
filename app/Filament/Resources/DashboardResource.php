<?php

namespace App\Filament\Resources;

use App\Filament\Resources\DashboardResource\Pages;
use App\Filament\Resources\DashboardResource\RelationManagers;
use App\Filament\Resources\DashboardResource\Widgets\TopAffiliatesWidget;
use App\Filament\Resources\DashboardResource\Widgets\AffiliateStatsOverview;
use App\Filament\Resources\DashboardResource\Widgets\AffiliatePerformanceChart;
use App\Filament\Resources\DashboardResource\Widgets\AffiliateCommissionOverview;
use App\Filament\Resources\DashboardResource\Widgets\AffiliateActivityChart;
use App\Filament\Resources\DashboardResource\Widgets\MonthlyComparisonChart;
 
use App\Filament\Resources\DashboardResource\Widgets\PayoutStatusWidget;
use App\Filament\Resources\DashboardResource\Widgets\RecentAffiliateActivities;
use App\Models\Dashboard;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class DashboardResource extends Resource
{
    protected static ?string $model = Dashboard::class;

    protected static ?string $navigationIcon = 'heroicon-o-chart-bar';
    protected static ?string $navigationGroup = 'Affiliates';
    protected static ?string $navigationLabel = 'Affiliate Dashboard';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                //
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                //
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
            // Use custom ListDashboards page to show widgets as dashboard
            'index' => Pages\ListDashboards::route('/'),
            'create' => Pages\CreateDashboard::route('/create'),
            'edit' => Pages\EditDashboard::route('/{record}/edit'),
        ];
    }

    public static function getWidgets(): array
    {
        return [
            AffiliateStatsOverview::class,
            AffiliateCommissionOverview::class,
            PayoutStatusWidget::class,
            AffiliatePerformanceChart::class,
            AffiliateActivityChart::class,
            MonthlyComparisonChart::class,
            TopAffiliatesWidget::class,
            RecentAffiliateActivities::class,
        ];
    }
}
