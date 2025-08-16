<?php

namespace App\Filament\Resources\AffiliateResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;

use Filament\Tables;
use Filament\Tables\Table;
use Lunar\Models\Product;
use Lunar\Models\ProductVariant;
use Illuminate\Database\Eloquent\Builder;

class ProductRatesRelationManager extends RelationManager
{
    protected static string $relationship = 'productRates';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Product Selection')
                    ->schema([
                        Forms\Components\Select::make('product_variant_id')
                            ->label('Product Variant')
                            ->options(function () {
                                return ProductVariant::with('product')
                                    ->get()
                                    ->mapWithKeys(function ($variant) {
                                        $productName = $variant->product->translateAttribute('name');
                                        $variantDetails = '';

                                        if ($variant->values->count() > 0) {
                                            $variantDetails = $variant->values->map(function ($value) {
                                                return $value->translate('name');
                                            })->join(', ');
                                        }

                                        $label = $productName;
                                        if ($variantDetails) {
                                            $label .= " - {$variantDetails}";
                                        } else {
                                            $label .= " - Variant #{$variant->id}";
                                        }

                                        // Use integer ID as key
                                        return [$variant->id => $label];
                                    })
                                    ->toArray();
                            })
                            ->searchable()
                            ->required()
                            ->helperText('Select a specific product variant to set commission rates.')
                            ->live(),
                    ]),
                // Forms\Components\Section::make('Referral Link')
                //     ->schema([
                //         Forms\Components\Placeholder::make('referral_link')
                //             ->label('Copy Referral Link')
                //             ->content(function (Forms\Get $get): ?string {
                //                 // Get the selected product_id from the form
                //                 $productId = $get('product_id');

                //                 if (!$productId) {
                //                     return null; 
                //                 }

                //                 $baseUrl = \Lunar\Models\Channel::first()?->url;

                //                 if (!$baseUrl) {
                //                     return 'Error: Base URL not configured in Lunar Channels.';
                //                 }

                //                 $slug = null;
                //                 if (str_starts_with($productId, 'product_')) {
                //                     $id = str_replace('product_', '', $productId);
                //                     $product = Product::find($id);
                //                     // Assuming Lunar products have a URL relation with a slug
                //                     $slug = $product?->url?->slug;
                //                 } elseif (str_starts_with($productId, 'variant_')) {
                //                     $id = str_replace('variant_', '', $productId);
                //                     // Eager load product and its URL relation
                //                     $variant = ProductVariant::with('product.url')->find($id);
                //                     $slug = $variant?->product?->url?->slug;
                //                 }

                //                 if (!$slug) {
                //                     return 'Error: Product or Variant slug not found.';
                //                 }

                //                 // Get the ID of the current affiliate (owner record of this relation manager)
                //                 $affiliateId = $this->getOwnerRecord()->id;

                //                 // Construct the full referral URL
                //                 // Ensure base URL ends without a slash and product path starts without one
                //                 $baseUrl = rtrim($baseUrl, '/');
                //                 $productPath = '/products/' . $slug;

                //                 return "{$baseUrl}{$productPath}?refid={$affiliateId}";
                //             })
                //             ->helperText('Share this link to earn commission on sales of the selected product/variant.')
                //             ->extraAttributes([
                //                 'x-data' => '{}', // Required for Alpine.js directives
                //                 'x-on:click' => '$clipboard(event.target.innerText)', // Copy to clipboard on click
                //                 'x-tooltip.raw' => 'Click to copy', // Tooltip on hover
                //                 'class' => 'cursor-pointer', // Indicate it's clickable
                //             ]),
                //     ])
                //     // Hide this section until a product/variant is selected
                //     ->hidden(fn (Forms\Get $get) => !$get('product_id')),
                Forms\Components\Section::make('Commission Settings')
                    ->schema([
                        Forms\Components\TextInput::make('rate')
                            ->label('Commission Rate')
                            ->numeric()
                            ->step(0.01)
                            ->required()
                            ->helperText('Enter the commission rate (e.g., 10 for 10% or 5.50 for $5.50)'),
                        
                        Forms\Components\Select::make('rate_type')
                            ->label('Rate Type')
                            ->options([
                                'percentage' => 'Percentage (%)',
                                'flat' => 'Flat Amount ($)'
                            ])
                            ->required()
                            ->default('percentage')
                            ->helperText('Choose whether the rate is a percentage of the sale or a flat amount.')
                            ->live(),

                        Forms\Components\Placeholder::make('commission_preview')
                            ->label('Commission Preview')
                            ->content(function (Forms\Get $get) {
                                $rate = $get('rate');
                                $rateType = $get('rate_type');
                                
                                if (!$rate || !$rateType) {
                                    return 'Enter rate and type to see preview';
                                }
                                
                                $sampleAmount = 100;
                                if ($rateType === 'percentage') {
                                    $commission = ($sampleAmount * $rate) / 100;
                                    return "For a \${$sampleAmount} sale: \${$commission} commission ({$rate}%)";
                                } else {
                                    return "For any sale: \${$rate} commission (flat rate)";
                                }
                            })
                            ->helperText('Example calculation based on your settings'),
                    ])
                    ->columns(2),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('id')
            ->columns([
                Tables\Columns\TextColumn::make('product_info')
                    ->label('Product Variant')
                    ->getStateUsing(function ($record) {
                        $variantId = $record->product_variant_id;
                        $variant = ProductVariant::with('product', 'values')->find($variantId);

                        if (!$variant) {
                            return 'Variant not found';
                        }

                        $name = $variant->product->translateAttribute('name');

                        if ($variant->values && $variant->values->count() > 0) {
                            $variantDetails = $variant->values->map(function ($value) {
                                return $value->translate('name');
                            })->join(', ');
                            $name .= " - {$variantDetails}";
                        } else {
                            $name .= " - Variant #{$variant->id}";
                        }

                        return $name;
                    })
                    ->searchable()
                    ->sortable(),
                
                Tables\Columns\TextColumn::make('rate')
                    ->label('Rate')
                    ->getStateUsing(function ($record) {
                        if ($record->rate_type === 'percentage') {
                            return $record->rate . '%';
                        }
                        return '$' . number_format($record->rate, 2);
                    })
                    ->sortable(),
                
                Tables\Columns\TextColumn::make('rate_type')
                    ->label('Type')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'percentage' => 'success',
                        'flat' => 'primary',
                        default => 'gray',
                    })
                    ->formatStateUsing(function ($state) {
                        return $state === 'percentage' ? 'Percentage' : 'Flat Amount';
                    }),
                
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Created')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('rate_type')
                    ->options([
                        'percentage' => 'Percentage',
                        'flat' => 'Flat Amount',
                    ]),
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make()
                    ->label('Add Product Rate')
                    ->icon('heroicon-o-plus')
                    ->mutateFormDataUsing(function (array $data): array {
                        // Validate that this affiliate doesn't already have a rate for this product variant
                        $existingRate = $this->getOwnerRecord()
                            ->productRates()
                            ->where('product_variant_id', $data['product_variant_id'])
                            ->first();
                        
                        if ($existingRate) {
                            throw new \Exception('A rate for this product variant already exists for this affiliate.');
                        }
                        
                        return $data;
                    }),
            ])
            ->actions([
                Tables\Actions\EditAction::make()
                    ->mutateFormDataUsing(function (array $data, $record): array {
                        // Validate that this affiliate doesn't already have a rate for this product variant
                        // (excluding the current record being edited)
                        $existingRate = $this->getOwnerRecord()
                            ->productRates()
                            ->where('product_variant_id', $data['product_variant_id'])
                            ->where('id', '!=', $record->id)
                            ->first();
                        
                        if ($existingRate) {
                            throw new \Exception('A rate for this product variant already exists for this affiliate.');
                        }   
                        
                        return $data;
                    }),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->emptyStateHeading('No Product Rates Set')
            ->emptyStateDescription('Set commission rates for specific products or variants to override the default affiliate rate.')
            ->emptyStateIcon('heroicon-o-currency-dollar');
    }
}
            