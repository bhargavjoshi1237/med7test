<?php

namespace App\Filament\Resources\AffiliateVisitResource\Pages;

use App\Filament\Resources\AffiliateVisitResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListAffiliateVisits extends ListRecords
{
    protected static string $resource = AffiliateVisitResource::class;

    protected function getHeaderActions(): array
    {
        return [
            // Visits are typically not created manually from the admin panel
        ];
    }
}
