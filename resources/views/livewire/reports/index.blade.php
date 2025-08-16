<div>
    <h1 class="text-2xl font-semibold text-gray-900">Reports</h1>

    <div class="mt-4">
        <!-- Tabs Navigation -->
        <div class="sm:hidden">
            <label for="tabs" class="sr-only">Select a tab</label>
            <select id="tabs" name="tabs" wire:model="currentTab" class="block w-full rounded-md border-gray-300 focus:border-indigo-500 focus:ring-indigo-500">
                <option value="referrals">Referrals</option>
                <option value="affiliates">Affiliates</option>
                <option value="sales">Sales</option>
                <option value="payouts">Payouts</option>
                <option value="visits">Visits</option>
                <option value="campaigns">Campaigns</option>
            </select>
        </div>
        <div class="hidden sm:block">
            <div class="border-b border-gray-200">
                <nav class="-mb-px flex space-x-8" aria-label="Tabs">
                    <a href="#" wire:click.prevent="selectTab('referrals')" class="{{ $currentTab === 'referrals' ? 'border-indigo-500 text-indigo-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }} whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm">
                        Referrals
                    </a>
                    <a href="#" wire:click.prevent="selectTab('affiliates')" class="{{ $currentTab === 'affiliates' ? 'border-indigo-500 text-indigo-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }} whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm">
                        Affiliates
                    </a>
                    <a href="#" wire:click.prevent="selectTab('sales')" class="{{ $currentTab === 'sales' ? 'border-indigo-500 text-indigo-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }} whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm">
                        Sales
                    </a>
                    <a href="#" wire:click.prevent="selectTab('payouts')" class="{{ $currentTab === 'payouts' ? 'border-indigo-500 text-indigo-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }} whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm">
                        Payouts
                    </a>
                    <a href="#" wire:click.prevent="selectTab('visits')" class="{{ $currentTab === 'visits' ? 'border-indigo-500 text-indigo-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }} whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm">
                        Visits
                    </a>
                     <a href="#" wire:click.prevent="selectTab('campaigns')" class="{{ $currentTab === 'campaigns' ? 'border-indigo-500 text-indigo-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }} whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm">
                        Campaigns
                    </a>
                </nav>
            </div>
        </div>

        <!-- Report Content -->
        <div class="mt-6">
            @if ($currentTab === 'referrals')
                <livewire:reports.referrals-report />
            @elseif ($currentTab === 'affiliates')
                <livewire:reports.affiliates-report />
            @elseif ($currentTab === 'sales')
                <livewire:reports.sales-report />
            @elseif ($currentTab === 'payouts')
                <livewire:reports.payouts-report />
            @elseif ($currentTab === 'visits')
                <livewire:reports.visits-report />
            @elseif ($currentTab === 'campaigns')
                <livewire:reports.campaigns-report />
            @endif
        </div>
    </div>
</div>