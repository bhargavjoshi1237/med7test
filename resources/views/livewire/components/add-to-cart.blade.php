<div>
    <div class="flex gap-4">
        <div class="flex items-center border border-gray-300 rounded-lg">
            <button type="button"
                    class="px-3 py-2 text-gray-600 hover:text-black"
                    onclick="(function(btn){ const input = btn.parentElement.querySelector('input[type=number]'); const v = parseInt(input.value||'1',10) - 1; input.value = v < 1 ? 1 : v; input.dispatchEvent(new Event('input', { bubbles: true })); })(this)">
                <i class="fa fa-minus text-sm" aria-hidden="true"></i>
            </button>

            <input class="w-16 text-center border-none focus:ring-0 text-lg font-medium no-spinner"
                   type="number"
                   id="quantity"
                   min="1"
                   value="1"
                   wire:model.live="quantity" />

            <style>
                input.no-spinner::-webkit-outer-spin-button,
                input.no-spinner::-webkit-inner-spin-button {
                    -webkit-appearance: none;
                    margin: 0;
                }
                input.no-spinner {
                    -moz-appearance: textfield;
                }
            </style>

            <button type="button"
                    class="px-3 py-2 text-gray-600 hover:text-black"
                    onclick="(function(btn){ const input = btn.parentElement.querySelector('input[type=number]'); const v = parseInt(input.value||'1',10) + 1; input.value = v; input.dispatchEvent(new Event('input', { bubbles: true })); })(this)">
                <i class="fa fa-plus text-sm" aria-hidden="true"></i>
            </button>
        </div>

        <button type="submit"
                class="w-full px-6 py-4 text-sm font-medium text-center text-white bg-indigo-600 rounded-lg hover:bg-indigo-700"
                wire:click.prevent="addToCart">
            Add to Cart
        </button>
    </div>

    @if ($errors->has('quantity'))
        <div class="p-2 mt-4 text-xs font-medium text-center text-red-700 rounded bg-red-50"
             role="alert">
            @foreach ($errors->get('quantity') as $error)
                {{ $error }}
            @endforeach
        </div>
    @endif
</div>
