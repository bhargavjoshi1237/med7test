<?php

namespace Lunar\Admin\Filament\Resources\ProductResource\Pages;

use Filament\Forms;
use Filament\Support\Facades\FilamentIcon;
use Filament\Tables;
use Filament\Tables\Table;
use Lunar\Admin\Events\ProductCollectionsUpdated;
use Lunar\Admin\Filament\Resources\ProductResource;
use Lunar\Admin\Support\Pages\BaseManageRelatedRecords;
use Lunar\Admin\Support\Tables\Columns\TranslatedTextColumn;
use Lunar\Models\Contracts\Collection as CollectionContract;

class ManageProductCollections extends BaseManageRelatedRecords
{
    protected static string $resource = ProductResource::class;

    protected static string $relationship = 'collections';

    public static function getNavigationIcon(): ?string
    {
        return FilamentIcon::resolve('lunar::collections');
    }

    public function getTitle(): string
    {
        return __('Collections');
    }

    public static function getNavigationLabel(): string
    {
        return __('Collections');
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('name')
            ->reorderable('position')
            ->columns([
                TranslatedTextColumn::make('attribute_data.name')
                    ->description(fn(CollectionContract $record): string => $record->breadcrumb->implode(' > '))
                    ->attributeData()
                    ->limitedTooltip()
                    ->limit(50)
                    ->label(__('lunarpanel::product.table.name.label')),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                Tables\Actions\AttachAction::make()
                    ->recordSelect(
                        function (Forms\Components\Select $select) {
                            return $select->placeholder(__('lunarpanel::product.pages.collections.select_collection'))
                                ->getSearchResultsUsing(static function (Forms\Components\Select $component, string $search, ManageProductCollections $livewire): array {
                                    $relationModel = $livewire->getRelationship()->getRelated()::class;

                                    // Get all collections and filter in PHP to avoid database-specific JSON functions
                                    return app($relationModel)
                                        ->get()
                                        ->filter(function (CollectionContract $record) use ($search) {
                                            $name = strtolower($record->translateAttribute('name') ?? '');
                                            $description = strtolower($record->translateAttribute('description') ?? '');
                                            $searchTerm = strtolower($search);

                                            return str_contains($name, $searchTerm) || str_contains($description, $searchTerm);
                                        })
                                        ->mapWithKeys(function (CollectionContract $record): array {
                                            $name = $record->translateAttribute('name') ?? 'Unnamed Collection';
                                            $breadcrumb = $record->breadcrumb->push($name)->join(' > ');
                                            return [$record->getKey() => $breadcrumb];
                                        })
                                        ->all();
                                })
                                ->getOptionLabelUsing(function ($value) {
                                    $collection = app(CollectionContract::class)->find($value);
                                    return $collection ? $collection->translateAttribute('name') : $value;
                                });
                        }
                    )->after(
                        fn() => ProductCollectionsUpdated::dispatch(
                            $this->getOwnerRecord()
                        )
                    ),
            ])
            ->actions([
                Tables\Actions\DetachAction::make()->after(
                    fn() => ProductCollectionsUpdated::dispatch(
                        $this->getOwnerRecord()
                    )
                ),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DetachBulkAction::make()->after(
                        fn() => ProductCollectionsUpdated::dispatch(
                            $this->getOwnerRecord()
                        )
                    ),
                ]),
            ]);
    }
}
