<?php

namespace App\Filament\Resources\BrandResource\Pages;

use App\Filament\Resources\BrandResource;
use Filament\Resources\Pages\Page;
use Lunar\Models\Contracts\Brand as BrandContract;

class CustomBrandPage extends Page
{
    protected static string $resource = BrandResource::class;

    protected static string $view = 'filament.resources.brand-resource.pages.custom-brand-page';

    public BrandContract $record;

    public static function getNavigationLabel(): string
    {
        return 'Custom';
    }

    public function mount(int | string $record): void
    {
        $this->record = $this->resolveRecord($record);
    }

    protected function resolveRecord(int | string $key): BrandContract
    {
        return static::getResource()::resolveRecordRouteBinding($key);
    }
}