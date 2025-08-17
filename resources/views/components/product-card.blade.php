@props(['product'])

<div class="bg-white rounded-lg shadow-sm   p-6 hover:shadow-md transition-shadow duration-300">
	<!-- Top: image + optional NEW badge -->
	<div class="relative flex justify-center mb-4">
		{{-- NEW badge if product marked new (adapt checks to your product model) --}}
		@if(
			(!empty($product->is_new) && $product->is_new) ||
			(!empty($product->getAttribute('is_new') ?? null)) ||
			(method_exists($product, 'hasTag') && $product->hasTag('new'))
		)
			<span class="absolute top-0 left-1/2 transform -translate-x-1/2 -translate-y-1/2 bg-sky-400 text-white text-xs font-bold px-3 py-1 rounded-full shadow">
				NEW
			</span>
		@endif

		@if ($product->thumbnail)
			<img class="h-48 w-auto object-contain" src="{{ $product->thumbnail->getUrl('medium') }}"
				alt="{{ $product->translateAttribute('name') }}" />
		@else
			<div class="h-48 w-32 bg-gray-100 rounded-lg flex items-center justify-center">
				<span class="text-gray-400">No Image</span>
			</div>
		@endif
	</div>

	<!-- Title -->
	<h3 class="text-sky-500 font-semibold text-lg mb-2 text-center">
		{{ $product->translateAttribute('name') }}
	</h3>

	<!-- Rating (falls back to 5) -->
	<div class="flex items-center justify-center mb-2">
		@php
			$rating = $product->rating ?? $product->average_rating ?? 5;
			$rounded = (int) round($rating);
		@endphp

		<div class="flex space-x-1">
			@for ($i = 1; $i <= 5; $i++)
				<svg class="h-4 w-4 {{ $i <= $rounded ? 'text-sky-500' : 'text-gray-300' }}" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
					<path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.286 3.97a1 1 0 00.95.69h4.178c.969 0 1.371 1.24.588 1.81l-3.38 2.455a1 1 0 00-.364 1.118l1.287 3.97c.3.922-.755 1.688-1.54 1.118l-3.38-2.455a1 1 0 00-1.176 0L5.82 17.06c-.784.57-1.838-.196-1.539-1.118l1.287-3.97a1 1 0 00-.364-1.118L1.824 6.38c-.783-.57-.38-1.81.588-1.81h4.178a1 1 0 00.95-.69l1.286-3.97z"/>
				</svg>
			@endfor
		</div>
	</div>

	<!-- Meta line (size | mg) - adjust keys to your model as needed -->
	<div class="text-center text-sm text-gray-500 mb-3">
		{{ $product->size ?? $product->getAttribute('size') ?? '2 oz/60 ml' }} |
		{{ $product->mg ?? $product->getAttribute('mg') ?? '4500 mg Hempzorb81™' }}
	</div>

	<!-- Price -->
	<div class="text-center mb-3">
		<span class="text-2xl font-bold text-black">
			<x-product-price :product="$product" />
		</span>
	</div>

	<!-- Subscribe note -->
	<div class="text-center text-xs text-gray-500 mb-4">
		subscribe and get <a href="#" class="text-sky-500 font-semibold underline">20% off</a>
	</div>

	<!-- CTA -->
	<div class="text-center mt-4">
		<a href="{{ route('product.view', $product->defaultUrl->slug) }}" wire:navigate
			class="inline-block bg-black text-white font-bold py-3 px-8 rounded-lg hover:bg-gray-800 transition-colors duration-200 uppercase tracking-wide">
			SELECT
		</a>
	</div>
</div>
