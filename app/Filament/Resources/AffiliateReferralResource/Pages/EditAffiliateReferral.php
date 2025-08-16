<?php

namespace App\Filament\Resources\AffiliateReferralResource\Pages;

use App\Filament\Resources\AffiliateReferralResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditAffiliateReferral extends EditRecord
{
    protected static string $resource = AffiliateReferralResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
