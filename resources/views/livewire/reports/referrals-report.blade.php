<div>
    {{-- HEADER ROW WITH MANAGE BUTTON --}}
    <div class="flex justify-end">
         <button wire:click="toggleManageReferrals" class="px-4 py-2 border border-primary-600 text-primary-600 rounded-lg text-sm font-medium hover:bg-primary-50 dark:hover:bg-primary-500/10 dark:text-primary-400 dark:border-primary-400">
            {{ $showManageReferrals ? 'Back to Dashboard' : 'Manage Referrals' }}
        </button>
    </div>

    {{-- STATE 1: DASHBOARD VIEW (Default) --}}
    @if (!$showManageReferrals)
        <div class="space-y-6 mt-6">
            <!-- Filters -->
            <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm p-4 flex items-center space-x-3 border dark:border-gray-700">
                <select wire:model.live="dashboardDateRange" class="filament-forms-input block w-full rounded-lg border-gray-300 shadow-sm dark:bg-gray-700 dark:border-gray-600">
                    <option value="last_week">Last Week</option>
                    <option value="this_week">This Week</option>
                    <option value="last_month">Last Month</option>
                    <option value="this_month">This Month</option>
                </select>
                <input type="text" wire:model.debounce.500ms="dashboardAffiliateName" placeholder="Affiliate name" class="filament-forms-input block w-full rounded-lg border-gray-300 shadow-sm dark:bg-gray-700 dark:border-gray-600">
                <button wire:click="runDashboardCalculations" class="filament-button bg-primary-600 text-white rounded-lg px-4 py-2 text-sm font-medium">Filter</button>
            </div>

            <!-- QUICK STATS (INLINE GRID) -->
            {{-- THIS IS THE FIX: A responsive grid to arrange cards horizontally --}}
            <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-5 gap-4">
                <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm p-4 text-center border dark:border-gray-700">
                    <p class="text-gray-500 dark:text-gray-400 text-xs mb-2">Paid Earnings</p>
                    <p class="text-2xl font-bold text-primary-600 dark:text-primary-400">${{ number_format($paidEarningsAllTime, 2) }}</p>
                    <p class="text-xs text-gray-400 mt-2">All Time</p>
                </div>
                <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm p-4 text-center border dark:border-gray-700">
                    <p class="text-gray-500 dark:text-gray-400 text-xs mb-2">Paid Earnings</p>
                    <p class="text-2xl font-bold text-primary-600 dark:text-primary-400">${{ number_format($paidEarningsPeriod, 2) }}</p>
                    <p class="text-xs text-gray-400 mt-2">{{ \Illuminate\Support\Str::of($dashboardDateRange)->replace('_', ' ')->title() }}</p>
                </div>
                <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm p-4 text-center border dark:border-gray-700">
                    <p class="text-gray-500 dark:text-gray-400 text-xs mb-2">Unpaid Earnings</p>
                    <p class="text-2xl font-bold text-primary-600 dark:text-primary-400">${{ number_format($unpaidEarningsPeriod, 2) }}</p>
                    <p class="text-xs text-gray-400 mt-2">{{ \Illuminate\Support\Str::of($dashboardDateRange)->replace('_', ' ')->title() }}</p>
                </div>
                <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm p-4 text-center border dark:border-gray-700">
                    <p class="text-gray-500 dark:text-gray-400 text-xs mb-2">Paid / Unpaid Referrals</p>
                    <p class="text-2xl font-bold text-primary-600 dark:text-primary-400">{{ $paidReferralsPeriod }} / {{ $unpaidReferralsPeriod }}</p>
                    <p class="text-xs text-gray-400 mt-2">{{ \Illuminate\Support\Str::of($dashboardDateRange)->replace('_', ' ')->title() }}</p>
                </div>
                <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm p-4 text-center border dark:border-gray-700">
                    <p class="text-gray-500 dark:text-gray-400 text-xs mb-2">Average Referral Amount</p>
                    <p class="text-2xl font-bold text-primary-600 dark:text-primary-400">${{ number_format($averageCommissionPeriod, 2) }}</p>
                    <p class="text-xs text-gray-400 mt-2">{{ \Illuminate\Support\Str::of($dashboardDateRange)->replace('_', ' ')->title() }}</p>
                </div>
            </div>

            <!-- Trends Chart -->
            <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm p-4 border dark:border-gray-700" wire:ignore>
                <div class="text-base font-semibold mb-2 text-gray-800 dark:text-white">Trends</div>
                <div class="relative h-72">
                    <canvas id="trendsChart"></canvas>
                </div>
            </div>
        </div>
    @endif
    
    {{-- STATE 2: MANAGE REFERRALS TABLE --}}
    @if ($showManageReferrals)
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm p-6 border dark:border-gray-700 space-y-4">
            <div class="flex justify-between items-center"><div class="flex items-center space-x-4 text-sm font-medium"><button wire:click="$set('statusFilter', '')" @class(['text-primary-600' => $statusFilter === '', 'text-gray-500 hover:text-primary-600' => $statusFilter !== ''])>All ({{$statusCounts['all']}})</button><span class="text-gray-300">|</span><button wire:click="$set('statusFilter', 'paid')" @class(['text-primary-600' => $statusFilter === 'paid', 'text-gray-500 hover:text-primary-600' => $statusFilter !== 'paid'])>Paid ({{$statusCounts['paid']}})</button><span class="text-gray-300">|</span><button wire:click="$set('statusFilter', 'unpaid')" @class(['text-primary-600' => $statusFilter === 'unpaid', 'text-gray-500 hover:text-primary-600' => $statusFilter !== 'unpaid'])>Unpaid ({{$statusCounts['unpaid']}})</button><span class="text-gray-300">|</span><button wire:click="$set('statusFilter', 'pending')" @class(['text-primary-600' => $statusFilter === 'pending', 'text-gray-500 hover:text-primary-600' => $statusFilter !== 'pending'])>Pending ({{$statusCounts['pending']}})</button><span class="text-gray-300">|</span><button wire:click="$set('statusFilter', 'rejected')" @class(['text-primary-600' => $statusFilter === 'rejected', 'text-gray-500 hover:text-primary-600' => $statusFilter !== 'rejected'])>Rejected ({{$statusCounts['rejected']}})</button></div><div class="flex items-center space-x-2"><input type="search" wire:model.live.debounce.300ms="search" placeholder="Search..." class="filament-forms-input block w-full rounded-lg border-gray-300 shadow-sm dark:bg-gray-700 dark:border-gray-600"></div></div>
            <div class="flex items-center space-x-4"><select wire:model.live="bulkAction" class="filament-forms-input rounded-lg border-gray-300 shadow-sm dark:bg-gray-700 dark:border-gray-600 text-sm"><option value="">Bulk actions</option><option value="mark_paid">Mark as Paid</option><option value="reject">Reject</option></select><button wire:click="applyBulkAction" class="filament-button text-sm">Apply</button><input type="text" wire:model.live.debounce.300ms="tableAffiliateName" placeholder="Affiliate name" class="filament-forms-input rounded-lg border-gray-300 shadow-sm dark:bg-gray-700 dark:border-gray-600 text-sm"><input type="date" wire:model.live="tableFromDate" class="filament-forms-input rounded-lg border-gray-300 shadow-sm dark:bg-gray-700 dark:border-gray-600 text-sm"><input type="date" wire:model.live="tableToDate" class="filament-forms-input rounded-lg border-gray-300 shadow-sm dark:bg-gray-700 dark:border-gray-600 text-sm"><select wire:model.live="tableType" class="filament-forms-input rounded-lg border-gray-300 shadow-sm dark:bg-gray-700 dark:border-gray-600 text-sm"><option value="">All Types</option><option value="percentage">Percentage</option><option value="flat">Flat</option></select></div>
            <div class="filament-tables-container rounded-lg border border-gray-200 dark:border-gray-700"><div class="overflow-x-auto"><table class="filament-tables-table w-full text-sm"><thead class="bg-gray-50 dark:bg-gray-800"><tr class="text-left"><th class="p-3 w-4"><input type="checkbox"/></th><th class="p-3">Referral ID</th><th class="p-3">Amount</th><th class="p-3">Affiliate</th><th class="p-3">Description</th><th class="p-3">Date</th><th class="p-3">Actions</th><th class="p-3">Status</th></tr></thead><tbody class="divide-y divide-gray-100 dark:divide-gray-700">
            @forelse ($referrals as $referral)
                <tr><td class="p-3"><input type="checkbox" wire:model.live="selectedReferrals" value="{{ $referral->id }}"/></td><td class="p-3">{{ $referral->id }}</td><td class="p-3 font-semibold">${{ number_format($referral->commission_amount, 2) }}</td><td class="p-3 text-primary-600 hover:underline">{{ $referral->affiliate->name ?? 'N/A' }}</td><td class="p-3 max-w-xs truncate">{{ $referral->description }}</td><td class="p-3">{{ $referral->created_at->format('M d, Y') }}</td><td class="p-3 text-primary-600 space-x-1"><a href="#" class="hover:underline">Accept</a><span>|</span><a href="#" class="hover:underline">Reject</a><span>|</span><a href="#" class="hover:underline text-red-600">Delete</a></td><td class="p-3"><span @class(['px-2 py-1 inline-flex text-xs leading-5 font-semibold rounded-full', 'bg-green-100 text-green-700' => $referral->status === 'paid', 'bg-yellow-100 text-yellow-700' => $referral->status === 'approved', 'bg-blue-100 text-blue-700' => $referral->status === 'pending', 'bg-red-100 text-red-700' => $referral->status === 'rejected'])>{{ $referral->status === 'approved' ? 'Unpaid' : ucfirst($referral->status) }}</span></td></tr>
            @empty
                <tr><td colspan="8"><div class="flex flex-col items-center justify-center p-12 text-center"><div class="mx-auto flex h-12 w-12 items-center justify-center rounded-full bg-gray-100 dark:bg-gray-700"><x-filament::icon icon="heroicon-o-arrows-right-left" class="h-6 w-6 text-gray-400"/></div><h3 class="mt-4 text-sm font-semibold text-gray-900 dark:text-white">No Referrals Found</h3><p class="mt-1 text-sm text-gray-500">There are no referrals that match your selected filters.</p></div></td></tr>
            @endforelse
            </tbody></table></div></div>
            <div class="mt-4">{{ $referrals->links() }}</div>
        </div>
    @endif

    @push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        document.addEventListener('livewire:initialized', () => {
            const ctx = document.getElementById('trendsChart');
            if (!ctx) return;
            let trendsChart = new Chart(ctx, { type: 'line', data: { labels: [], datasets: [] }, options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { display: false } }, scales: { y: { beginAtZero: true } } } });
            
            @this.on('trendsChartDataUpdated', (event) => {
                if(trendsChart) { trendsChart.data.labels = event[0].labels; trendsChart.data.datasets = event[0].datasets; trendsChart.update(); }
            });
            setTimeout(() => { @this.dispatch('runDashboardCalculations'); }, 50);
        });
    </script>
    @endpush
</div>