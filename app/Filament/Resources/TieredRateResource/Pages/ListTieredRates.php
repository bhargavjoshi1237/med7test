<?php

namespace App\Filament\Resources\TieredRateResource\Pages;

use App\Filament\Resources\TieredRateResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListTieredRates extends ListRecords
{
    protected static string $resource = TieredRateResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
