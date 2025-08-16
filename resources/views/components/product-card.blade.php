@props(['product'])

<div class="bg-white rounded-lg shadow-sm border border-gray-100 p-6 hover:shadow-md transition-shadow duration-300">
    <!-- Product Image -->
    <div class="flex justify-center mb-4">
        @if ($product->thumbnail)
            <img class="h-48 w-auto object-contain" src="{{ $product->thumbnail->getUrl('medium') }}"
                alt="{{ $product->translateAttribute('name') }}" />
        @else
            <div class="h-48 w-32 bg-gray-100 rounded-lg flex items-center justify-center">
                <span class="text-gray-400">No Image</span>
            </div>
        @endif
    </div>

    <!-- Product Title -->
    <h3 class="text-sky-500 font-semibold text-lg mb-2 text-center">
        {{ $product->translateAttribute('name') }}
    </h3>



    <div class="text-center mb-3">
        <span class="text-2xl font-bold text-black">
            <x-product-price :product="$product" />
        </span>
    </div>

    

    <div class="text-center">
        <a href="{{ route('product.view', $product->defaultUrl->slug) }}" wire:navigate
            class="inline-block bg-black text-white font-bold py-3 px-8 rounded-lg hover:bg-gray-800 transition-colors duration-200 uppercase tracking-wide">
            Select
        </a>
    </div>
</div>
