<?php

namespace App\Filament\Resources\AffiliateReferralResource\Pages;

use App\Filament\Resources\AffiliateReferralResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListAffiliateReferrals extends ListRecords
{
    protected static string $resource = AffiliateReferralResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
