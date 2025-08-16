<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\ProductIndexRequest;
use Illuminate\Http\JsonResponse;
use Lunar\Models\Product;

class ProductController extends Controller
{
    /**
     * Display a listing of products.
     */
    public function index(ProductIndexRequest $request): JsonResponse
    {
        $query = Product::with([
            'variants.basePrices.currency',
            'variants.values.option',
            'media',
            'collections'
        ]);
        $filteredQuery = $request->applyFilters($query);
        $totalCount = $filteredQuery->count();
        $products = $request->applyPagination($filteredQuery)
            ->get()
            ->map(function ($product) {
                return [
                    'id' => $product->id,
                    'name' => $product->translateAttribute('name'),
                    'slug' => $product->defaultUrl?->slug,
                    'description' => $product->translateAttribute('description'),
                    'thumbnail' => $product->thumbnail?->getUrl(),
                    'status' => $product->status,
                    'collections' => $product->collections->map(function ($collection) {
                        return [
                            'id' => $collection->id,
                            'name' => $collection->translateAttribute('name'),
                            'slug' => $collection->defaultUrl?->slug,
                        ];
                    }),
                    'variants' => $product->variants->map(function ($variant) {
                        return [
                            'id' => $variant->id,
                            'sku' => $variant->sku,
                            'stock' => $variant->stock,
                            'price' => $variant->basePrices->first()?->price->formatted(),
                            'options' => $variant->values->map(function ($value) {
                                return [
                                    'option' => $value->option->translate('name'),
                                    'value' => $value->translate('name'),
                                ];
                            }),
                        ];
                    }),
                ];
            });

        return response()->json([
            'data' => $products,
            'meta' => $request->getPaginationMeta($totalCount),
            'filters' => $request->getAppliedFilters(),
        ]);
    }
}