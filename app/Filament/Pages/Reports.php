<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;

class Reports extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-chart-bar-square';
    protected static string $view = 'filament.pages.reports';
    protected static ?string $navigationGroup = 'Reports';
    protected static ?string $title = 'Affiliates Report';

    public string $activeTab = 'referrals';
}