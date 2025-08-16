<?php

use Illuminate\Database\Eloquent\Builder;
use Lunar\Base\Traits\Searchable;
use Lunar\Models\Attribute;
use Lunar\FieldTypes\TranslatedText;

if (! function_exists('get_search_builder')) {
    function get_search_builder(string $model, string $search): Laravel\Scout\Builder|Builder
    {
        $scoutEnabled = config('lunar.panel.scout_enabled', false);
        $isScoutSearchable = in_array(Searchable::class, class_uses_recursive($model));

        if (
            $scoutEnabled &&
            $isScoutSearchable
        ) {
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