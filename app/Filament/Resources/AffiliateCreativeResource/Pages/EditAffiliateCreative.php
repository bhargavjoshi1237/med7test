<?php

namespace App\Filament\Resources\AffiliateCreativeResource\Pages;

use App\Filament\Resources\AffiliateCreativeResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditAffiliateCreative extends EditRecord
{
    protected static string $resource = AffiliateCreativeResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
