<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Database\Eloquent\Builder;
use Lunar\Base\Traits\Searchable;

class SearchServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        // Override the get_search_builder function behavior by monkey-patching
        // This is a workaround for SQLite compatibility
        if (!function_exists('get_search_builder_sqlite_safe')) {
            function get_search_builder_sqlite_safe(string $model, string $search): Laravel\Scout\Builder|Builder
            {
                $scoutEnabled = config('lunar.panel.scout_enabled', false);
                $isScoutSearchable = in_array(Searchable::class, class_uses_recursive($model));

                if ($scoutEnabled && $isScoutSearchable) {
                    return $model::search($search);
                } else {
                    // For SQLite compatibility, we'll do a simple search on all records
                    // and filter in PHP to avoid JSON function issues
                    $query = $model::query();
                    
                    // Get all records and filter in PHP to avoid database-specific JSON functions
                    $allRecords = $query->get();
                    $matchingIds = [];
                    
                    foreach ($allRecords as $record) {
                        $name = strtolower($record->translateAttribute('name') ?? '');
                        $description = strtolower($record->translateAttribute('description') ?? '');
                        $searchTerm = strtolower($search);
                        
                        // Check if search term matches name or description
                        if (str_contains($name, $searchTerm) || str_contains($description, $searchTerm)) {
                            $matchingIds[] = $record->getKey();
                        }
                    }
                    
                    // Return a query builder that filters by the matching IDs
                    return $model::query()->whereIn($query->getModel()->getKeyName(), $matchingIds);
                }
            }
        }
    }
}