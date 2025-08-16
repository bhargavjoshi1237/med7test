<?php

namespace App\Filament\Resources\DashboardResource\Pages;

use App\Filament\Resources\DashboardResource;
use Filament\Actions;
use App\Filament\Resources\DashboardResource\Widgets\TopAffiliatesWidget;
use App\Filament\Resources\DashboardResource\Widgets\AffiliateStatsOverview;
use App\Filament\Resources\DashboardResource\Widgets\AffiliatePerformanceChart;
use App\Filament\Resources\DashboardResource\Widgets\AffiliateCommissionOverview;
use App\Filament\Resources\DashboardResource\Widgets\AffiliateActivityChart;
use App\Filament\Resources\DashboardResource\Widgets\MonthlyComparisonChart;

use App\Filament\Resources\DashboardResource\Widgets\PayoutStatusWidget;
use App\Filament\Resources\DashboardResource\Widgets\RecentAffiliateActivities;
use Filament\Resources\Pages\ListRecords;

class ListDashboards extends ListRecords
{
    // This page acts as a dashboard, not a typical list page
    protected static ?string $title = 'Affiliate Dashboard';
    protected static string $resource = DashboardResource::class;

    protected function getHeaderActions(): array
    {
        return [
            // Actions\CreateAction::make(),
        ];
    }

    // Show widgets as dashboard on the list page
    protected function getHeaderWidgets(): array
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

    // Disable breadcrumbs
    public function getBreadcrumbs(): array
    {
        return [];
    }



    // Override to return proper query (required by Filament)
    protected function getTableQuery(): \Illuminate\Database\Eloquent\Builder
    {
        return static::getResource()::getEloquentQuery();
    }

    // Keep the empty state messages visible if needed
    public function getEmptyStateHeading(): ?string
    {
        return 'No dashboards';
    }

    public function getEmptyStateDescription(): ?string
    {
        return 'Dashboard widgets are displayed above.';
    }
}
