<?php

namespace App\Filament\Resources\AffiliateGroupResource\Pages;

use App\Filament\Resources\AffiliateGroupResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditAffiliateGroup extends EditRecord
{
    protected static string $resource = AffiliateGroupResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
