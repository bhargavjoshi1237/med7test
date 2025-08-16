<?php

namespace App\Filament\Resources\TieredRateResource\Pages;

use App\Filament\Resources\TieredRateResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditTieredRate extends EditRecord
{
    protected static string $resource = TieredRateResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
