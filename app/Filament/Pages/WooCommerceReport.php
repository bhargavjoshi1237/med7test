<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;

class WooCommerceReport extends Page
{
    // A relevant icon for e-commerce
    protected static ?string $navigationIcon = 'heroicon-o-shopping-cart';

    // The view file that this page will render
    protected static string $view = 'filament.pages.woocommerce-report';

    // Set the title that appears at the top of the page
    protected static ?string $title = 'WooCommerce Report';

    // Place this new page in the same navigation group for organization
    protected static ?string $navigationGroup = 'Reports';

    // This property holds the state for the active tab, defaulting to 'orders'
    public string $activeTab = 'orders';

     public function getTabsProperty(): array
    {
        return [
            'orders' => ['label' => 'Orders', 'icon' => 'heroicon-m-shopping-bag'],
            'customers' => ['label' => 'Customers', 'icon' => 'heroicon-m-user-group'],
        ];
    }
}