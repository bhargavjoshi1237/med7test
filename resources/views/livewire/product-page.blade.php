<section class="bg-white">
    <div class="max-w-screen-xl px-4 py-12 mx-auto sm:px-6 lg:px-8">
        {{-- Breadcrumb: Home > Products > Product Name --}}
       

        <div class="grid items-start grid-cols-1 gap-12 md:grid-cols-2">
            <!-- Left Section: Product Images -->
            <div class="space-y-6">
                <div class="flex justify-center">
                    @php
                        $largeUrls = [];
                        foreach ($this->images as $img) {
                            $largeUrls[] = $img->getUrl('large');
                        }
                    @endphp

                    @if (count($largeUrls))
                        <div id="mainImageWrap" class="relative w-full h-96 overflow-hidden flex items-center justify-center">
                            <!-- simplified single main image (no animation classes) -->
                            <img id="mainImage"
                                 class="h-[28rem] w-auto object-contain"
                                 src="{{ $largeUrls[0] }}"
                                 alt="{{ $this->product->translateAttribute('name') }}" />
                        </div>
                    @else
                        <div class="h-96 w-64 bg-gray-100 rounded-lg flex items-center justify-center">
                            <span class="text-gray-400">No Image</span>
                        </div>
                    @endif
                </div>
                        <br>
                <!-- Thumbnail Images -->
                <div class="flex justify-center space-x-4">
                    @foreach ($this->images as $image)
                    <div
                        class="w-36 h-36 rounded-lg overflow-hidden cursor-pointer hover:border-sky-400 transition-colors"
                        wire:key="thumbnail_{{ $image->id }}"
                        data-large="{{ $image->getUrl('large') }}">
                        <img loading="lazy"
                             class="w-full h-full object-cover"
                             src="{{ $image->getUrl('small') }}"
                             alt="{{ $this->product->translateAttribute('name') }}" />
                    </div>
                    @endforeach
                </div>

                @once
                <script>
                    document.addEventListener('DOMContentLoaded', function () {
                        const images = @json($largeUrls);
                        if (!images || images.length === 0) return;

                        const mainImg = document.getElementById('mainImage');
                        const thumbs = Array.from(document.querySelectorAll('[data-large]'));

                        function setThumbHighlight(i) {
                            thumbs.forEach((el, j) => {
                                if (j === i) el.classList.add('border-sky-400');
                                else el.classList.remove('border-sky-400');
                            });
                        }

                        // initialize highlight
                        setThumbHighlight(0);

                        // thumbnail clicks: immediately show image (no animation) and update highlight
                        thumbs.forEach((el, j) => {
                            el.addEventListener('click', function () {
                                const src = el.getAttribute('data-large');
                                if (src) {
                                    mainImg.src = src;
                                }
                                setThumbHighlight(j);
                            });
                        });
                    });
                </script>
                @endonce
            </div>

            <!-- Right Section: Product Information -->
            <div class="space-y-6 -mt-5 -ml-6">
                <!-- Product Title -->
                  <nav class="mb-6 text-sm" aria-label="Breadcrumb">
            <ol class="flex items-center space-x-2">
                <li>
                    <a href="{{ url('/') }}" class="text-sky-500 hover:underline">Home</a>
                </li>
                <li class="text-gray-300">/</li>
                <li>
                    <a href="{{ url('/products') }}" class="text-sky-500 hover:underline">Products</a>
                </li>
                <li class="text-gray-300">/</li>
                <li class="text-gray-700" aria-current="page">
                    {{ $this->product->translateAttribute('name') }}
                </li>
            </ol>
        </nav>
                <h1 class="text-3xl font-bold text-black">
                    {{ $this->product->translateAttribute('name') }}
                </h1>

                <!-- Product Specifications -->
                <p class="text-gray-600 text-lg">
                    {{ $this->product->translateAttribute('name') }}
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
                    {{-- Normalize description (may be stored as array) and render rich HTML from the editor --}}
                    @php
                        $description = $this->product->translateAttribute('description');
                        if (is_array($description)) {
                            $description = implode('', $description);
                        }
                        $description = trim((string) $description);
                    @endphp

                    @if ($description !== '')
                        {{-- Scoped styles ensure rich HTML (h3, ul, li, p) looks like the commented example --}}
                        <style>
                            .product-description ul { list-style: none; padding-left: 0; margin: .5rem 0; }
                            .product-description li { display: flex; align-items: flex-start; gap: .75rem; margin: .35rem 0; }
                            .product-description li::before {
                                content: "";
                                width: .5rem;
                                height: .5rem;
                                background-color: rgb(14 165 233); /* sky-400 */
                                border-radius: 9999px;
                                margin-top: .55rem;
                                flex: 0 0 auto;
                            }
                            .product-description h3 { font-weight: 600; color: #000; margin-top: .75rem; margin-bottom: .4rem; }
                            .product-description p { margin-bottom: .6rem; }
                        </style>

                        <div class="mb-4 product-description prose max-w-none prose-slate">
                            {!! $description !!}
                        </div>
                    @endif
                </div>

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
                    
                    <livewire:components.add-to-cart :purchasable="$this->variant"
                        :refferer="$this->refferer"
                        :wire:key="$this->variant->id">
                </div>

                <!-- Shipping/Guarantee Information -->
                <div class="text-sm text-gray-600 text-start">
                    <p>Free shipping on all orders over $50. 30-day satisfaction guaranteed.</p>
                </div>
            </div>
        </div>
    </div>
</section>