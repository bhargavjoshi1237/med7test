<?php

namespace App\Filament\Resources;

use Illuminate\Database\Eloquent\Builder;
use Lunar\Admin\Filament\Resources\CollectionResource as BaseCollectionResource;

class CollectionResource extends BaseCollectionResource
{
    public static function getGlobalSearchEloquentQuery(): Builder
    {
        return static::getEloquentQuery()
            ->with(['group'])
            ->when(
                filled($search = request()->input('search')),
                function (Builder $query) use ($search) {
                    $query->where(function (Builder $subQuery) use ($search) {
                        // Search in group name
                        $subQuery->whereHas('group', function (Builder $groupQuery) use ($search) {
                            $groupQuery->where('name', 'like', "%{$search}%");
                        })
                        // Search in attribute_data as text (works with SQLite)
                        ->orWhere('attribute_data', 'like', "%{$search}%");
                    });
                }
            );
    }
}