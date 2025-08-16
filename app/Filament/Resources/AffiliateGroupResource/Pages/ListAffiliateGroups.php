<?php

namespace App\Filament\Resources\AffiliateGroupResource\Pages;

use App\Filament\Resources\AffiliateGroupResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListAffiliateGroups extends ListRecords
{
    protected static string $resource = AffiliateGroupResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
