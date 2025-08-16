<div class="space-y-6 relative">
    {{-- Loading State --}}
    <div wire:loading.delay wire:target="runCalculations, updatedDateRange, applyCustomDateRange" class="absolute inset-0 bg-white/80 dark:bg-gray-900/80 z-10 flex items-center justify-center rounded-lg">
        <div class="flex items-center space-x-2"><x-filament::loading-indicator class="h-5 w-5" /><span class="text-sm text-gray-600 dark:text-gray-400">Updating report...</span></div>
    </div>

    {{-- Date Range Filters --}}
    <div class="bg-white dark:bg-gray-800 rounded-lg border dark:border-gray-700 p-4">
        <div class="flex flex-wrap items-center gap-3">
            <div class="flex flex-wrap gap-2">
                @foreach($this->getDateRangeOptions() as $key => $label)
                    <button type="button" wire:click="$set('dateRange', '{{ $key }}')" @class(['px-3 py-2 text-sm font-medium rounded-lg transition-all duration-200', 'bg-primary-100 text-primary-700 dark:bg-primary-500/20 dark:text-primary-300' => $dateRange === $key, 'bg-gray-50 text-gray-700 hover:bg-gray-100 dark:bg-gray-700 dark:text-gray-300 dark:hover:bg-gray-600' => $dateRange !== $key])>{{ $label }}</button>
                @endforeach
            </div>
            <div class="flex items-center gap-2 ml-auto">
                <div class="text-sm text-gray-500 dark:text-gray-400">Custom:</div>
                <div class="relative"><input type="date" wire:model.live="fromDate" class="block w-36 text-sm rounded-lg border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white" max="{{ now()->format('Y-m-d') }}">@error('fromDate')<div class="absolute top-full left-0 mt-1 text-xs text-red-600 dark:text-red-400">{{ $message }}</div>@enderror</div>
                <span class="text-gray-400">to</span>
                <div class="relative"><input type="date" wire:model.live="toDate" class="block w-36 text-sm rounded-lg border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white" max="{{ now()->format('Y-m-d') }}">@error('toDate')<div class="absolute top-full left-0 mt-1 text-xs text-red-600 dark:text-red-400">{{ $message }}</div>@enderror</div>
                <button type="button" wire:click="applyCustomDateRange" class="px-4 py-2 bg-primary-600 text-white text-sm font-medium rounded-lg hover:bg-primary-700 focus:ring-2 focus:ring-primary-500 focus:ring-offset-2 transition-colors">Apply</button>
            </div>
            <div class="flex items-center gap-2"><button type="button" wire:click="$dispatch('export-csv')" class="flex items-center gap-2 px-3 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 focus:ring-2 focus:ring-primary-500 focus:ring-offset-2 dark:bg-gray-700 dark:text-gray-300 dark:border-gray-600 dark:hover:bg-gray-600 transition-colors"><x-heroicon-o-arrow-down-tray class="w-4 h-4"/>Export CSV</button></div>
        </div>
    </div>

    {{-- Stats Cards --}}
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
        <div class="bg-white dark:bg-gray-800 rounded-lg border dark:border-gray-700 p-6"><div class="flex items-center justify-between"><div><p class="text-sm font-medium text-gray-600 dark:text-gray-400">Gross Sales</p><p class="text-2xl font-bold text-gray-900 dark:text-white">${{ number_format($stats['gross_sales'] ?? 0, 2) }}</p></div><div class="p-3 bg-blue-100 dark:bg-blue-900/50 rounded-lg"><x-heroicon-o-banknotes class="w-6 h-6 text-blue-600 dark:text-blue-400"/></div></div></div>
        <div class="bg-white dark:bg-gray-800 rounded-lg border dark:border-gray-700 p-6"><div class="flex items-center justify-between"><div><p class="text-sm font-medium text-gray-600 dark:text-gray-400">Orders</p><p class="text-2xl font-bold text-gray-900 dark:text-white">{{ number_format($stats['orders_placed'] ?? 0) }}</p></div><div class="p-3 bg-green-100 dark:bg-green-900/50 rounded-lg"><x-heroicon-o-shopping-bag class="w-6 h-6 text-green-600 dark:text-green-400"/></div></div></div>
        <div class="bg-white dark:bg-gray-800 rounded-lg border dark:border-gray-700 p-6"><div class="flex items-center justify-between"><div><p class="text-sm font-medium text-gray-600 dark:text-gray-400">Avg. Order Value</p><p class="text-2xl font-bold text-gray-900 dark:text-white">${{ number_format($stats['average_order_value'] ?? 0, 2) }}</p></div><div class="p-3 bg-purple-100 dark:bg-purple-900/50 rounded-lg"><x-heroicon-o-chart-bar class="w-6 h-6 text-purple-600 dark:text-purple-400"/></div></div></div>
        <div class="bg-white dark:bg-gray-800 rounded-lg border dark:border-gray-700 p-6"><div class="flex items-center justify-between"><div><p class="text-sm font-medium text-gray-600 dark:text-gray-400">Items Sold</p><p class="text-2xl font-bold text-gray-900 dark:text-white">{{ number_format($stats['items_purchased'] ?? 0) }}</p></div><div class="p-3 bg-orange-100 dark:bg-orange-900/50 rounded-lg"><x-heroicon-o-cube class="w-6 h-6 text-orange-600 dark:text-orange-400"/></div></div></div>
    </div>
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        <div class="bg-white dark:bg-gray-800 rounded-lg border dark:border-gray-700 p-4"><div class="flex items-center gap-3"><div class="p-2 bg-indigo-100 dark:bg-indigo-900/50 rounded-lg"><x-heroicon-o-calculator class="w-5 h-5 text-indigo-600 dark:text-indigo-400"/></div><div><p class="text-sm font-medium text-gray-600 dark:text-gray-400">Net Sales</p><p class="text-lg font-semibold text-gray-900 dark:text-white">${{ number_format($stats['net_sales'] ?? 0, 2) }}</p></div></div></div>
        <div class="bg-white dark:bg-gray-800 rounded-lg border dark:border-gray-700 p-4"><div class="flex items-center gap-3"><div class="p-2 bg-cyan-100 dark:bg-cyan-900/50 rounded-lg"><x-heroicon-o-truck class="w-5 h-5 text-cyan-600 dark:text-cyan-400"/></div><div><p class="text-sm font-medium text-gray-600 dark:text-gray-400">Shipping</p><p class="text-lg font-semibold text-green-600 dark:text-green-400">${{ number_format($stats['shipping_charged'] ?? 0, 2) }}</p></div></div></div>
        <div class="bg-white dark:bg-gray-800 rounded-lg border dark:border-gray-700 p-4"><div class="flex items-center gap-3"><div class="p-2 bg-yellow-100 dark:bg-yellow-900/50 rounded-lg"><x-heroicon-o-ticket class="w-5 h-5 text-yellow-600 dark:text-yellow-400"/></div><div><p class="text-sm font-medium text-gray-600 dark:text-gray-400">Discounts</p><p class="text-lg font-semibold text-yellow-600 dark:text-yellow-400">${{ number_format($stats['coupons_used_value'] ?? 0, 2) }}</p></div></div></div>
    </div>

    {{-- Chart --}}
    <div class="bg-white dark:bg-gray-800 rounded-lg border dark:border-gray-700" x-data="{ chart: null }" x-init="
        const salesCtx = document.getElementById('salesChart').getContext('2d');
        chart = new Chart(salesCtx, {
            type: 'line', data: { labels: [], datasets: [{ label: 'Sales ($)', data: [], borderColor: '#3b82f6', backgroundColor: 'rgba(59, 130, 246, 0.1)', fill: true, tension: 0.4 }] },
            options: { responsive: true, maintainAspectRatio: false, interaction: { intersect: false, mode: 'index' }, plugins: { legend: { display: false } }, scales: { x: { grid: { display: false } }, y: { beginAtZero: true } } }
        });
        Livewire.on('salesChartDataUpdated', (data) => {
            if (chart) { chart.data.labels = data.labels || []; chart.data.datasets[0].data = data.data || []; chart.update('none'); }
        });
    ">
        <div class="p-6 border-b border-gray-200 dark:border-gray-700"><h3 class="text-lg font-medium text-gray-900 dark:text-white">Sales Trend</h3><p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Daily sales performance for the selected period</p></div>
        <div class="p-6" wire:ignore><div class="relative h-80"><canvas id="salesChart"></canvas></div></div>
    </div>
</div>