<x-filament-panels::page>
    {{--
        The @php block is now correctly removed from the view.
        The $tabs array should now be a public property or a computed property
        in your WooCommerceReport.php component class.
    --}}

    <div role="tablist" class="flex items-center border-b border-gray-200 dark:border-white/10">
        {{-- THIS IS THE FIX: Access the 'tabs' property from the component instance --}}
        @foreach ($this->tabs as $tabKey => $tab)
            <button
                type="button"
                role="tab"
                aria-selected="{{ $activeTab === $tabKey ? 'true' : 'false' }}"
                aria-controls="report-content-panel"
                wire:click="$set('activeTab', '{{ $tabKey }}')"
                @class([
                    'group flex items-center gap-x-2 whitespace-nowrap px-3 py-3 text-sm font-medium border-b-2 -mb-px transition-colors',
                    'text-primary-600 border-primary-500' => $activeTab === $tabKey,
                    'text-gray-500 border-transparent hover:text-gray-700 hover:border-gray-300 dark:text-gray-400 dark:hover:text-white dark:hover:border-gray-500' => $activeTab !== $tabKey,
                ])
            >
                <x-filament::icon
                    :icon="$tab['icon']"
                    @class([
                        'h-5 w-5',
                        'text-primary-600' => $activeTab === $tabKey,
                        'text-gray-400 group-hover:text-gray-500 dark:text-gray-500 dark:group-hover:text-gray-400' => $activeTab !== $tabKey,
                    ])
                />
                <span>{{ $tab['label'] }}</span>
            </button>
        @endforeach
    </div>

    <div class="mt-6">
        <div wire:loading.delay.long wire:target="activeTab" class="w-full flex justify-center py-8">
            <x-filament::loading-indicator class="h-10 w-10" />
        </div>

        <div wire:loading.remove wire:target="activeTab" id="report-content-panel" role="tabpanel">
            <x-filament::card>
                <div wire:key="{{ $activeTab }}">
                    @switch($activeTab)
                        @case('orders')
                            @livewire(\App\Livewire\Reports\OrdersReport::class)
                            @break
                        @case('customers')
                            @livewire(\App\Livewire\Reports\CustomersReport::class)
                            @break
                    @endswitch
                </div>
            </x-filament::card>
        </div>
    </div>

</x-filament-panels::page>