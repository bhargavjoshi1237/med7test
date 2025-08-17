<?php

namespace App\Lunar\Extensions;

use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Contracts\Pagination\Paginator;
use Lunar\Admin\Support\Extending\ListPageExtension;
use Lunar\Admin\Filament\Widgets;

class ListBrands extends ListPageExtension
{   
    protected ?string $heading = 'Brands List';

    protected static bool $shouldRegisterNavigation = false;
    public function heading($title): string
    {
        return 'Sub Brands Under Med - 7';
    }



    public function headerActions(array $actions): array
    { 
        $actions = [
            ...$actions,
          
                // Action::make('View on Storefront'),
            
        ];

        return $actions;
    }
    
}
