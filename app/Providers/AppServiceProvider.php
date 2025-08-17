<?php

namespace App\Providers;

use App\Modifiers\ShippingModifier;
use Illuminate\Support\ServiceProvider;
use Lunar\Admin\Support\Facades\LunarPanel;
use Lunar\Base\ShippingModifiers;
use Lunar\Shipping\ShippingPlugin;
use App\Filament\Plugins\AffiliatePlugin;
use Lunar\Models\Order;
use Lunar\Admin\Filament\Resources;
use App\Observers\OrderObserver;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    protected static $resources = [
        Resources\ActivityResource::class,
        Resources\AttributeGroupResource::class,
        Resources\ChannelResource::class,
        Resources\CollectionGroupResource::class,
        // \App\Lunar\Extensions\BrandResources::class,
        \App\Filament\Resources\CollectionResource::class, // Use custom CollectionResource for search fix
        Resources\CurrencyResource::class,
        Resources\CustomerGroupResource::class,
        Resources\CustomerResource::class,
        Resources\DiscountResource::class,
        Resources\LanguageResource::class,
        Resources\OrderResource::class,
        Resources\ProductOptionResource::class,
        Resources\ProductResource::class,
        Resources\ProductTypeResource::class,
        Resources\ProductVariantResource::class,
        Resources\StaffResource::class,
        Resources\TagResource::class,
        Resources\TaxClassResource::class,
        Resources\TaxZoneResource::class,
        Resources\TaxRateResource::class,
    ];

    public static function getResources(): array
    {
        return static::$resources;
    }   
    
    public function register(): void
    {   
        
        LunarPanel::panel(
            
            fn($panel) => $panel->path('admin')->brandName('Med - 7')->brandLogo('')->darkModeBrandLogo('')           
            ->navigationGroups([
                'Catalog',
                'Sales',
              
            ])
            ->favicon('')->resources([])->plugins([
                new ShippingPlugin,
                AffiliatePlugin::make()
            ])

        )
             ->register();

        // Register Lunar extensions
        LunarPanel::extensions([
            \Lunar\Admin\Filament\Resources\ProductResource\Pages\EditProduct::class => \App\Lunar\Extensions\ProductEditExtension::class,
            \Lunar\Admin\Filament\Resources\ProductResource\Pages\ManageProductCollections::class => \App\Lunar\Extensions\ProductCollectionExtension::class,
            // \Lunar\Admin\Filament\Resources\BrandResource::class => \App\Lunar\Extensions\BrandResources::class
            \Lunar\Admin\Filament\Resources\ProductTypeResource::class => \App\Lunar\Extensions\ProductTypeResource::class,
            \Lunar\Admin\Filament\Resources\ProductResource\Pages\ManageProductAssociations::class => \App\Lunar\Extensions\ProductAssociationExtension::class,
            // \Lunar\Admin\Filament\Resources\OrderResource\Pages\ManageOrder::class => \App\Lunar\Admin\Filament\Resources\ManageOrder::class,
               \Lunar\Admin\Filament\Resources\BrandResources\Pages\ListBrands::class => \App\Lunar\Extensions\ListBrands::class,

        ]); 
             
             // LunarPanel::panel(fn($panel) => $panel->plugin())
        //     ->register();
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(ShippingModifiers $shippingModifiers): void
    {
        $shippingModifiers->add(
            ShippingModifier::class
        );

        \Lunar\Facades\ModelManifest::replace(
            \Lunar\Models\Contracts\Product::class,
            \App\Models\Product::class,
            // \App\Models\CustomProduct::class,
        );

        Order::observe(OrderObserver::class);
    }
}
