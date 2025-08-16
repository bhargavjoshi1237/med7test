<div class="space-y-6">
    {{-- Loading State --}}
    <div wire:loading.delay wire:target="runCalculations,toggleCustomerList" class="absolute inset-0 bg-white/80 dark:bg-gray-900/80 z-10 flex items-center justify-center rounded-lg">
        <div class="flex items-center space-x-2">
            <x-filament::loading-indicator class="h-5 w-5" />
            <span class="text-sm text-gray-600 dark:text-gray-400">Loading...</span>
        </div>
    </div>

    {{-- Header Actions --}}
    <div class="flex items-center justify-between">
        <div>
            <h2 class="text-xl font-semibold text-gray-900 dark:text-white">
                {{ $showCustomerList ? 'Customer Directory' : 'Customer Analytics' }}
            </h2>
            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                {{ $showCustomerList ? 'Browse and manage customer records' : 'Track customer acquisition and engagement metrics' }}
            </p>
        </div>
        
        <button 
            type="button"
            wire:click="toggleCustomerList" 
            class="flex items-center gap-2 px-4 py-2 text-sm font-medium transition-colors rounded-lg border-2 {{ $showCustomerList ? 'bg-gray-100 text-gray-700 border-gray-200 hover:bg-gray-200 dark:bg-gray-700 dark:text-gray-300 dark:border-gray-600 dark:hover:bg-gray-600' : 'bg-primary-50 text-primary-700 border-primary-200 hover:bg-primary-100 dark:bg-primary-900 dark:text-primary-300 dark:border-primary-700 dark:hover:bg-primary-800' }}"
        >
            @if($showCustomerList)
                <x-heroicon-o-chart-bar class="w-4 h-4" />
                Back to Analytics
            @else
                <x-heroicon-o-users class="w-4 h-4" />
                View Customer List
            @endif
        </button>
    </div>

    @if (!$showCustomerList)
        {{-- Dashboard View --}}
        <div class="space-y-6">
            {{-- Date Range Filters --}}
            <div class="bg-white dark:bg-gray-800 rounded-lg border dark:border-gray-700 p-4">
                <div class="flex flex-wrap items-center gap-3">
                    {{-- Quick Range Buttons --}}
                    <div class="flex flex-wrap gap-2">
                        @foreach($this->getDateRangeOptions() as $key => $label)
                            @if($key !== 'custom')
                                <button 
                                    type="button"
                                    wire:click="$set('dateRange', '{{ $key }}')"
                                    @class([
                                        'px-3 py-2 text-sm font-medium rounded-lg transition-all duration-200',
                                        'bg-primary-100 text-primary-700 border-2 border-primary-200 dark:bg-primary-900 dark:text-primary-300 dark:border-primary-700' => $dateRange === $key,
                                        'bg-gray-50 text-gray-700 border-2 border-transparent hover:bg-gray-100 hover:border-gray-200 dark:bg-gray-700 dark:text-gray-300 dark:hover:bg-gray-600' => $dateRange !== $key
                                    ])
                                >
                                    {{ $label }}
                                </button>
                            @endif
                        @endforeach
                    </div>

                    {{-- Custom Date Range --}}
                    <div class="flex items-center gap-2 ml-auto">
                        <div class="text-sm text-gray-500 dark:text-gray-400">Custom:</div>
                        
                        <div class="relative">
                            <input 
                                type="date" 
                                wire:model.lazy="fromDate"
                                class="block w-36 text-sm rounded-lg border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white"
                                max="{{ now()->format('Y-m-d') }}"
                            >
                            @error('fromDate')
                                <div class="absolute top-full left-0 mt-1 text-xs text-red-600 dark:text-red-400">{{ $message }}</div>
                            @enderror
                        </div>
                        
                        <span class="text-gray-400">to</span>
                        
                        <div class="relative">
                            <input 
                                type="date" 
                                wire:model.lazy="toDate"
                                class="block w-36 text-sm rounded-lg border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white"
                                max="{{ now()->format('Y-m-d') }}"
                            >
                            @error('toDate')
                                <div class="absolute top-full left-0 mt-1 text-xs text-red-600 dark:text-red-400">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>
            </div>

            {{-- Stats Grid --}}
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                {{-- New Signups --}}
                <div class="bg-white dark:bg-gray-800 rounded-lg border dark:border-gray-700 p-6">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm font-medium text-gray-600 dark:text-gray-400">New Signups</p>
                            <p class="text-2xl font-bold text-gray-900 dark:text-white">
                                {{ number_format($stats['new_signups'] ?? 0) }}
                            </p>
                            <div class="flex items-center mt-2">
                                @php
                                    $growthRate = $stats['growth_rate'] ?? 0;
                                    $isPositive = $growthRate > 0;
                                    $isNegative = $growthRate < 0;
                                @endphp
                                @if($growthRate != 0)
                                    <div @class([
                                        'flex items-center text-xs font-medium',
                                        'text-green-600 dark:text-green-400' => $isPositive,
                                        'text-red-600 dark:text-red-400' => $isNegative,
                                        'text-gray-500 dark:text-gray-400' => !$isPositive && !$isNegative
                                    ])>
                                        @if($isPositive)
                                            <x-heroicon-s-arrow-trending-up class="w-3 h-3 mr-1" />
                                            +{{ abs($growthRate) }}%
                                        @elseif($isNegative)
                                            <x-heroicon-s-arrow-trending-down class="w-3 h-3 mr-1" />
                                            {{ $growthRate }}%
                                        @endif
                                        <span class="ml-1 text-gray-500">vs previous period</span>
                                    </div>
                                @endif
                            </div>
                        </div>
                        <div class="p-3 bg-blue-100 dark:bg-blue-900 rounded-lg">
                            <x-heroicon-o-user-plus class="w-6 h-6 text-blue-600 dark:text-blue-400" />
                        </div>
                    </div>
                </div>

                {{-- Total Customers --}}
                <div class="bg-white dark:bg-gray-800 rounded-lg border dark:border-gray-700 p-6">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm font-medium text-gray-600 dark:text-gray-400">Total Customers</p>
                            <p class="text-2xl font-bold text-gray-900 dark:text-white">
                                {{ number_format($stats['total_customers'] ?? 0) }}
                            </p>
                            <p class="text-xs text-gray-500 dark:text-gray-400 mt-2">
                                {{ number_format($stats['average_signups_per_day'] ?? 0, 1) }} avg. daily signups
                            </p>
                        </div>
                        <div class="p-3 bg-green-100 dark:bg-green-900 rounded-lg">
                            <x-heroicon-o-users class="w-6 h-6 text-green-600 dark:text-green-400" />
                        </div>
                    </div>
                </div>

                {{-- Conversion Rate --}}
                <div class="bg-white dark:bg-gray-800 rounded-lg border dark:border-gray-700 p-6">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm font-medium text-gray-600 dark:text-gray-400">Conversion Rate</p>
                            <p class="text-2xl font-bold text-gray-900 dark:text-white">
                                {{ number_format($stats['conversion_rate'] ?? 0, 1) }}%
                            </p>
                            <p class="text-xs text-gray-500 dark:text-gray-400 mt-2">
                                {{ number_format($stats['customers_with_orders'] ?? 0) }} customers with orders
                            </p>
                        </div>
                        <div class="p-3 bg-purple-100 dark:bg-purple-900 rounded-lg">
                            <x-heroicon-o-chart-pie class="w-6 h-6 text-purple-600 dark:text-purple-400" />
                        </div>
                    </div>
                </div>
            </div>

            {{-- Chart --}}
            <div class="bg-white dark:bg-gray-800 rounded-lg border dark:border-gray-700">
                <div class="p-6 border-b border-gray-200 dark:border-gray-700">
                    <h3 class="text-lg font-medium text-gray-900 dark:text-white">Customer Acquisition Trend</h3>
                    <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                        New customer signups over the selected period
                    </p>
                </div>
                
                <div 
                    class="p-6" 
                    wire:ignore
                    x-data="{
                        chart: null,
                        initChart() {
                            const ctx = document.getElementById('customerChart');
                            if (!ctx) return;
                            
                            if (this.chart) this.chart.destroy();
                            
                            this.chart = new Chart(ctx, {
                                type: 'line',
                                data: {
                                    labels: [],
                                    datasets: [{
                                        label: 'New Signups',
                                        data: [],
                                        borderColor: '#10b981',
                                        backgroundColor: 'rgba(16, 185, 129, 0.1)',
                                        pointBackgroundColor: '#ffffff',
                                        pointBorderColor: '#10b981',
                                        pointBorderWidth: 2,
                                        pointRadius: 4,
                                        pointHoverRadius: 6,
                                        tension: 0.4,
                                        fill: true
                                    }]
                                },
                                options: {
                                    responsive: true,
                                    maintainAspectRatio: false,
                                    interaction: {
                                        intersect: false,
                                        mode: 'index'
                                    },
                                    plugins: {
                                        legend: { display: false },
                                        tooltip: {
                                            backgroundColor: 'rgba(0, 0, 0, 0.8)',
                                            titleColor: '#ffffff',
                                            bodyColor: '#ffffff',
                                            borderColor: '#10b981',
                                            borderWidth: 1,
                                            cornerRadius: 8,
                                            callbacks: {
                                                label: function(context) {
                                                    return 'Signups: ' + context.parsed.y;
                                                }
                                            }
                                        }
                                    },
                                    scales: {
                                        x: {
                                            grid: { display: false },
                                            border: { color: '#e5e7eb' }
                                        },
                                        y: {
                                            beginAtZero: true,
                                            grid: { color: '#f3f4f6' },
                                            border: { color: '#e5e7eb' },
                                            ticks: {
                                                stepSize: Math.max(1, Math.ceil(Math.max(...({{ json_encode($chartData['data'] ?? []) }}) || [1]) / 5)),
                                                callback: function(value) {
                                                    return Math.floor(value) === value ? value : '';
                                                }
                                            }
                                        }
                                    }
                                }
                            });

                            @this.on('customerChartDataUpdated', (event) => {
                                if (this.chart && event.data) {
                                    this.chart.data.labels = event.data.labels || [];
                                    this.chart.data.datasets[0].data = event.data.data || [];
                                    this.chart.update('none');
                                }
                            });

                            $wire.dispatch('loadCustomerChartData');
                        }
                    }" 
                    x-init="initChart()"
                >
                    <div class="relative h-80">
                        <canvas id="customerChart"></canvas>
                    </div>
                </div>
            </div>
        </div>
    @endif

    @if ($showCustomerList)
        {{-- Customer List View --}}
        <div class="space-y-6">
            {{-- Search and Filters --}}
            <div class="bg-white dark:bg-gray-800 rounded-lg border dark:border-gray-700 p-4">
                <div class="flex flex-col sm:flex-row gap-4">
                    <div class="flex-1">
                        <div class="relative">
                            <input 
                                type="search" 
                                wire:model.live.debounce.300ms="search" 
                                placeholder="Search by name or email..."
                                class="block w-full pl-10 pr-3 py-2 text-sm border border-gray-300 rounded-lg bg-gray-50 focus:ring-primary-500 focus:border-primary-500 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-primary-500 dark:focus:border-primary-500"
                            >
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <x-heroicon-o-magnifying-glass class="h-4 w-4 text-gray-400" />
                            </div>
                        </div>
                    </div>
                    
                    <button 
                        type="button"
                        wire:click="$dispatch('export-customers-csv')"
                        class="flex items-center gap-2 px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 focus:ring-2 focus:ring-primary-500 focus:ring-offset-2 dark:bg-gray-700 dark:text-gray-300 dark:border-gray-600 dark:hover:bg-gray-600 transition-colors"
                    >
                        <x-heroicon-o-arrow-down-tray class="w-4 h-4" />
                        Export CSV
                    </button>
                </div>
            </div>

            {{-- Customer Table --}}
            <div class="bg-white dark:bg-gray-800 rounded-lg border dark:border-gray-700 overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                        <thead class="bg-gray-50 dark:bg-gray-900">
                            <tr>
                                <th class="px-6 py-3 text-left">
                                    <button 
                                        type="button"
                                        wire:click="sortBy('first_name')"
                                        class="flex items-center gap-1 text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider hover:text-gray-700 dark:hover:text-gray-300"
                                    >
                                        Name
                                        @if($sortBy === 'first_name')
                                            @if($sortDirection === 'asc')
                                                <x-heroicon-s-chevron-up class="w-3 h-3" />
                                            @else
                                                <x-heroicon-s-chevron-down class="w-3 h-3" />
                                            @endif
                                        @endif
                                    </button>
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                    Email
                                </th>
                                <th class="px-6 py-3 text-left">
                                    <button 
                                        type="button"
                                        wire:click="sortBy('orders_count')"
                                        class="flex items-center gap-1 text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider hover:text-gray-700 dark:hover:text-gray-300"
                                    >
                                        Orders
                                        @if($sortBy === 'orders_count')
                                            @if($sortDirection === 'asc')
                                                <x-heroicon-s-chevron-up class="w-3 h-3" />
                                            @else
                                                <x-heroicon-s-chevron-down class="w-3 h-3" />
                                            @endif
                                        @endif
                                    </button>
                                </th>
                                <th class="px-6 py-3 text-left">
                                    <button 
                                        type="button"
                                        wire:click="sortBy('orders_sum_total')"
                                        class="flex items-center gap-1 text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider hover:text-gray-700 dark:hover:text-gray-300"
                                    >
                                        Total Spend
                                        @if($sortBy === 'orders_sum_total')
                                            @if($sortDirection === 'asc')
                                                <x-heroicon-s-chevron-up class="w-3 h-3" />
                                            @else
                                                <x-heroicon-s-chevron-down class="w-3 h-3" />
                                            @endif
                                        @endif
                                    </button>
                                </th>
                                <th class="px-6 py-3 text-left">
                                    <button 
                                        type="button"
                                        wire:click="sortBy('created_at')"
                                        class="flex items-center gap-1 text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider hover:text-gray-700 dark:hover:text-gray-300"
                                    >
                                        Registered
                                        @if($sortBy === 'created_at')
                                            @if($sortDirection === 'asc')
                                                <x-heroicon-s-chevron-up class="w-3 h-3" />
                                            @else
                                                <x-heroicon-s-chevron-down class="w-3 h-3" />
                                            @endif
                                        @endif
                                    </button>
                                </th>
                            </tr>
                        </thead>
                        <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                            @forelse ($this->customers as $customer)
                                <tr class="hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors">
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="flex items-center">
                                            <div class="flex-shrink-0 h-8 w-8">
                                                <div class="h-8 w-8 rounded-full bg-primary-100 dark:bg-primary-900 flex items-center justify-center">
                                                    <span class="text-xs font-medium text-primary-700 dark:text-primary-300">
                                                        {{ strtoupper(substr($customer->first_name ?? 'C', 0, 1)) }}{{ strtoupper(substr($customer->last_name ?? 'U', 0, 1)) }}
                                                    </span>
                                                </div>
                                            </div>
                                            <div class="ml-4">
                                                <div class="text-sm font-medium text-gray-900 dark:text-white">
                                                    {{ $customer->fullName }}
                                                </div>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm text-gray-900 dark:text-white">
                                            {{ $customer->users->first()?->email ?? 'N/A' }}
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="flex items-center">
                                            <span class="text-sm font-medium text-gray-900 dark:text-white">
                                                {{ $customer->orders_count ?? 0 }}
                                            </span>
                                            @if(($customer->orders_count ?? 0) > 0)
                                                <span class="ml-2 inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200">
                                                    Active
                                                </span>
                                            @else
                                                <span class="ml-2 inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300">
                                                    No Orders
                                                </span>
                                            @endif
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm font-medium text-gray-900 dark:text-white">
                                            ${{ number_format(($customer->orders_sum_total ?? 0) / 100, 2) }}
                                        </div>
                                        @if(($customer->orders_sum_total ?? 0) > 0)
                                            <div class="text-xs text-gray-500 dark:text-gray-400">
                                                ${{ number_format((($customer->orders_sum_total ?? 0) / 100) / max(1, $customer->orders_count ?? 1), 2) }} avg.
                                            </div>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm text-gray-900 dark:text-white">
                                            {{ $customer->created_at->format('M d, Y') }}
                                        </div>
                                        <div class="text-xs text-gray-500 dark:text-gray-400">
                                            {{ $customer->created_at->diffForHumans() }}
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="px-6 py-12 text-center">
                                        <div class="flex flex-col items-center">
                                            <x-heroicon-o-users class="w-12 h-12 text-gray-400 dark:text-gray-500 mb-4" />
                                            <h3 class="text-sm font-medium text-gray-900 dark:text-white mb-1">No customers found</h3>
                                            <p class="text-sm text-gray-500 dark:text-gray-400">
                                                @if($search)
                                                    Try adjusting your search terms or clearing the search.
                                                @else
                                                    Get started by adding your first customer.
                                                @endif
                                            </p>
                                            @if($search)
                                                <button 
                                                    type="button"
                                                    wire:click="$set('search', '')"
                                                    class="mt-3 text-sm text-primary-600 hover:text-primary-500 dark:text-primary-400 dark:hover:text-primary-300"
                                                >
                                                    Clear search
                                                </button>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                {{-- Pagination --}}
                @if($this->customers->hasPages())
                    <div class="px-6 py-4 border-t border-gray-200 dark:border-gray-700">
                        {{ $this->customers->links() }}
                    </div>
                @endif
            </div>

            {{-- Customer Stats Summary --}}
            @if($this->customers->count() > 0)
                <div class="bg-gray-50 dark:bg-gray-900 rounded-lg p-4">
                    <div class="flex items-center justify-between text-sm text-gray-600 dark:text-gray-400">
                        <span>
                            Showing {{ $this->customers->firstItem() ?? 0 }} to {{ $this->customers->lastItem() ?? 0 }} 
                            of {{ $this->customers->total() }} customers
                        </span>
                        <div class="flex items-center gap-4">
                            <span>
                                Active Customers: {{ $this->customers->where('orders_count', '>', 0)->count() }}
                            </span>
                            <span>
                                Total Revenue: ${{ number_format($this->customers->sum(fn($c) => ($c->orders_sum_total ?? 0) / 100), 2) }}
                            </span>
                        </div>
                    </div>
                </div>
            @endif
        </div>
    @endif

    @push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        document.addEventListener('livewire:initialized', () => {
            // Handle CSV exports
            @this.on('download-customers-csv', (event) => {
                // Implement CSV download logic here
                console.log('Exporting customers CSV:', event);
                
                // Example implementation:
                // const csvContent = convertToCSV(event.data);
                // const blob = new Blob([csvContent], { type: 'text/csv' });
                // const url = window.URL.createObjectURL(blob);
                // const a = document.createElement('a');
                // a.setAttribute('hidden', '');
                // a.setAttribute('href', url);
                // a.setAttribute('download', event.filename);
                // document.body.appendChild(a);
                // a.click();
                // document.body.removeChild(a);
            });
        });
    </script>
    @endpush
</div>