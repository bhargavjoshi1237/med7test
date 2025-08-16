<x-filament-panels::page>

    {{-- NEW SLIDER-STYLE TABS TO MATCH YOUR IMAGE --}}
    <div class="relative border-b-2 border-gray-200 dark:border-gray-700">
        <div class="flex items-center space-x-4 overflow-x-auto pb-0">
            @php
                $tabs = [
                    'referrals' => ['label' => 'Referrals', 'icon' => 'heroicon-m-arrows-right-left'],
                    'affiliates' => ['label' => 'Affiliates', 'icon' => 'heroicon-m-users'],
                    'sales' => ['label' => 'Sales', 'icon' => 'heroicon-m-receipt-percent'],
                    'payouts' => ['label' => 'Payouts', 'icon' => 'heroicon-m-banknotes'],
                    'visits' => ['label' => 'Visits', 'icon' => 'heroicon-m-cursor-arrow-rays'],
                ];
            @endphp

            @foreach ($tabs as $tabKey => $tab)
                <button
                    type="button"
                    wire:click="$set('activeTab', '{{ $tabKey }}')"
                    @class([
                        'relative group inline-flex items-center gap-x-2 whitespace-nowrap px-3 py-4 text-sm font-medium transition-colors',
                        'text-primary-600' => $activeTab === $tabKey,
                        'text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-white' => $activeTab !== $tabKey,
                    ])
                >
                    <x-filament::icon :icon="$tab['icon']" class="h-5 w-5" />
                    <span>{{ $tab['label'] }}</span>

                    @if ($activeTab === $tabKey)
                        <div class="absolute bottom-[-2px] left-0 w-full h-0.5 bg-primary-600 rounded-full"></div>
                    @endif
                </button>
            @endforeach
        </div>
    </div>

    {{-- The section that loads the active tab's content --}}
    <div class="mt-6">
        <div wire:key="{{ $activeTab }}">
            @switch($activeTab)
                @case('referrals')
                    @livewire(\App\Livewire\Reports\ReferralsReport::class)
                    @break
                @case('affiliates')
                    @livewire(\App\Livewire\Reports\AffiliatesReport::class)
                    @break
                @case('sales')
                    @livewire(\App\Livewire\Reports\SalesReport::class)
                    @break
                @case('payouts')
                    @livewire(\App\Livewire\Reports\PayoutsReport::class)
                    @break
                @case('visits')
                    @livewire(\App\Livewire\Reports\VisitsReport::class)
                    @break
            @endswitch
        </div>
    </div>

</x-filament-panels::page>