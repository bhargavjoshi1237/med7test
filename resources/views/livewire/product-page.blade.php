<section class="bg-white">
    <div class="max-w-screen-xl px-4 py-12 mx-auto sm:px-6 lg:px-8">
        <div class="grid items-start grid-cols-1 gap-12 md:grid-cols-2">
            <!-- Left Section: Product Images -->
            <div class="space-y-6">
                <!-- Main Product Image -->
                <div class="flex justify-center">
                    @if ($this->image)
                    <img class="h-96 w-auto object-contain"
                        src="{{ $this->image->getUrl('large') }}"
                        alt="{{ $this->product->translateAttribute('name') }}" />
                    @else
                    <div class="h-96 w-64 bg-gray-100 rounded-lg flex items-center justify-center">
                        <span class="text-gray-400">No Image</span>
                    </div>
                    @endif
                </div>

                <!-- Thumbnail Images -->
                <div class="flex justify-center space-x-4">
                    @foreach ($this->images as $image)
                    <div class="w-20 h-20 border-2 border-gray-200 rounded-lg overflow-hidden cursor-pointer hover:border-sky-400 transition-colors"
                        wire:key="thumbnail_{{ $image->id }}">
                        <img loading="lazy"
                            class="w-full h-full object-cover"
                            src="{{ $image->getUrl('small') }}"
                            alt="{{ $this->product->translateAttribute('name') }}" />
                    </div>
                    @endforeach
                </div>
            </div>

            <!-- Right Section: Product Information -->
            <div class="space-y-6">
                <!-- Product Title -->
                <h1 class="text-3xl font-bold text-black">
                    {{ $this->product->translateAttribute('name') }}
                </h1>

                <!-- Product Specifications -->
                <p class="text-gray-600 text-lg">
                    @if ($this->variant)
                    2 oz/60 ml | 4500 mg Hempzorb81™ Full Spectrum Hemp CBD Oil
                    @else
                    2 oz/60 ml | 4500 mg Hempzorb81™ Full Spectrum Hemp CBD Oil
                    @endif
                </p>

                <!-- Customer Rating -->
                <div class="flex items-center space-x-2">
                    <div class="flex space-x-1">
                        @for ($i = 1; $i <= 5; $i++)
                            <i class="fa fa-star text-black text-lg"></i>
                            @endfor
                    </div>
                    <a href="#reviews" class="text-sky-500 hover:text-sky-600 font-medium">
                        13 reviews
                    </a>
                </div>

                <!-- Price -->
                <div class="text-3xl font-bold text-black">
                    <x-product-price :variant="$this->variant" />
                </div>

                <!-- Product Description -->
                <div class="text-gray-700 leading-relaxed">
                    <p>
                        The first and only full spectrum hemp oil with clinically proven metabolic support.
                        Med 7 showed significant results in 2 placebo controlled, multi-centered studies:
                    </p>
                </div>

                <!-- Benefits -->
                <div class="space-y-2">
                    <h3 class="font-semibold text-black">Benefits:</h3>
                    <ul class="space-y-1 text-gray-700">
                        <li class="flex items-center">
                            <span class="w-2 h-2 bg-sky-400 rounded-full mr-3"></span>
                            Blood glucose support
                        </li>
                        <li class="flex items-center">
                            <span class="w-2 h-2 bg-sky-400 rounded-full mr-3"></span>
                            Weight management
                        </li>
                        <li class="flex items-center">
                            <span class="w-2 h-2 bg-sky-400 rounded-full mr-3"></span>
                            Supports healthy sleep
                        </li>
                        <li class="flex items-center">
                            <span class="w-2 h-2 bg-sky-400 rounded-full mr-3"></span>
                            Supports joint health and mobility
                        </li>
                    </ul>
                </div>

                <!-- Suggested Use -->
                <div class="space-y-2">
                    <h3 class="font-semibold text-black">Suggested Use:</h3>
                    <p class="text-gray-700">Take 2-3 ml daily in single or divided doses.</p>
                </div>

                <!-- Flavor -->
                <div class="space-y-2">
                    <h3 class="font-semibold text-black">Flavor:</h3>
                    <p class="text-gray-700">Cool Mint</p>
                </div>

                <!-- Product Options -->
                @if ($this->productOptions)
                <div class="space-y-4">
                    @foreach ($this->productOptions as $option)
                    <fieldset>
                        <legend class="text-sm font-semibold text-black mb-2">
                            {{ $option['option']->translate('name') }}
                        </legend>

                        <div class="flex flex-wrap gap-2"
                            x-data="{
                                         selectedOption: @entangle('selectedOptionValues').live,
                                         selectedValues: [],
                                     }"
                            x-init="selectedValues = Object.values(selectedOption);
                                     $watch('selectedOption', value =>
                                         selectedValues = Object.values(selectedOption)
                                     )">
                            @foreach ($option['values'] as $value)
                            <button class="px-4 py-2 text-sm font-medium border rounded-lg focus:outline-none focus:ring-2 focus:ring-sky-400 transition-colors"
                                type="button"
                                wire:click="
                                                $set('selectedOptionValues.{{ $option['option']->id }}', {{ $value->id }})
                                            "
                                :class="{
                                                    'bg-sky-500 border-sky-500 text-white hover:bg-sky-600': selectedValues
                                                        .includes({{ $value->id }}),
                                                    'border-gray-300 hover:bg-gray-50': !selectedValues.includes({{ $value->id }})
                                                }">
                                {{ $value->translate('name') }}
                            </button>
                            @endforeach
                        </div>
                    </fieldset>
                    @endforeach
                </div>
                @endif

                <!-- Quantity and Add to Cart -->
                <div class="flex items-center space-x-4">
                    <div class="flex items-center border border-gray-300 rounded-lg">
                        <button type="button" class="px-3 py-2 text-gray-600 hover:text-black">
                            <i class="fa fa-minus text-sm"></i>
                        </button>
                        <input type="number" value="1" min="1"
                            class="w-16 text-center border-none focus:ring-0 text-lg font-medium">
                        <button type="button" class="px-3 py-2 text-gray-600 hover:text-black">
                            <i class="fa fa-plus text-sm"></i>
                        </button>
                    </div>

                    <livewire:components.add-to-cart :purchasable="$this->variant"
                        :refferer="$this->refferer"
                        :wire:key="$this->variant->id">
                </div>


                <!-- Shipping/Guarantee Information -->
                <div class="text-sm text-gray-600 text-center">
                    <p>Free shipping on all orders over $50. 30-day satisfaction guaranteed.</p>
                </div>
            </div>
        </div>
    </div>
</section>