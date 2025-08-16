<div class="space-y-6">

    <div class="flex justify-end">
        <button wire:click="toggleManageAffiliates"
            class="px-4 py-2 border border-primary-600 text-primary-600 rounded-lg text-sm font-medium hover:bg-primary-50 dark:hover:bg-primary-500/10 dark:text-primary-400 dark:border-primary-400">
            {{ $showManageAffiliates ? 'Back to Dashboard' : 'Manage Affiliates' }}
        </button>
    </div>

    {{-- STATE 1: DASHBOARD VIEW (Default) --}}
    @if (!$showManageAffiliates)
        <div class="space-y-6">
            <!-- Filters -->
            <div
                class="bg-white dark:bg-gray-800 rounded-xl shadow-sm p-4 flex items-center space-x-3 border dark:border-gray-700">
                <select wire:model.live="dashboardDateRange"
                    class="filament-forms-input block w-full rounded-lg border-gray-300 shadow-sm dark:bg-gray-700 dark:border-gray-600">
                    <option value="this_month">This Month</option>
                    <option value="last_month">Last Month</option>
                    <option value="this_week">This Week</option>
                    <option value="last_week">Last Week</option>
                    <option value="this_year">This Year</option>
                </select>
                <button wire:click="runDashboardCalculations"
                    class="filament-button bg-primary-600 text-white rounded-lg px-4 py-2 text-sm font-medium">Filter</button>
            </div>

            <!-- QUICK STATS (VERTICAL STACK) -->
            {{-- THIS IS THE FIX: A vertical space container instead of a grid --}}
            {{-- <div class="space-y-4"> --}}
                <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-5 gap-4">
                    <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm p-4 text-center border dark:border-gray-700">
                        <p class="text-sm text-gray-500 dark:text-gray-400">Total Affiliates</p>
                        <p class="text-3xl font-bold text-primary-600 dark:text-primary-400 mt-1">{{ $totalAffiliates }}</p>
                        <p class="text-xs text-gray-400 mt-1">All Time</p>
                    </div>

                    <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm p-4 text-center border dark:border-gray-700">
                        <p class="text-sm text-gray-500 dark:text-gray-400">New Affiliates</p>
                        @if($newAffiliatesCount > 0)
                            <p class="text-3xl font-bold text-primary-600 dark:text-primary-400 mt-1">{{ $newAffiliatesCount }}
                            </p>
                        @else
                            <p class="text-gray-500 text-lg py-3">No data</p>
                        @endif
                        <p class="text-xs text-gray-400 mt-1">
                            {{ \Illuminate\Support\Str::of($dashboardDateRange)->replace('_', ' ')->title() }}</p>
                    </div>

                    <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm p-4 text-center border dark:border-gray-700">
                        <p class="text-sm text-gray-500 dark:text-gray-400">Top Earning Affiliate</p>
                        @if($topEarningAffiliateName)
                            <p class="text-xl font-bold text-primary-600 dark:text-primary-400 mt-1 truncate">
                                {{ $topEarningAffiliateName }}</p>
                        @else
                            <p class="text-gray-500 text-lg py-3">No data</p>
                        @endif
                        <p class="text-xs text-gray-400 mt-1">
                            {{ \Illuminate\Support\Str::of($dashboardDateRange)->replace('_', ' ')->title() }}</p>
                    </div>

                    <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm p-4 text-center border dark:border-gray-700">
                        <p class="text-sm text-gray-500 dark:text-gray-400">Highest Converting Affiliate</p>
                        @if($highestConvertingAffiliateName)
                            <p class="text-xl font-bold text-primary-600 dark:text-primary-400 mt-1 truncate">
                                {{ $highestConvertingAffiliateName }}</p>
                        @else
                            <p class="text-gray-500 text-lg py-3">No data</p>
                        @endif
                        <p class="text-xs text-gray-400 mt-1">
                            {{ \Illuminate\Support\Str::of($dashboardDateRange)->replace('_', ' ')->title() }}</p>
                    </div>

                </div>

                <!-- Trends Chart -->
                <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm p-4 border dark:border-gray-700" wire:ignore>
                    <div class="text-base font-semibold mb-2 text-gray-800 dark:text-white">Trends</div>
                    <div class="relative h-72">
                        <canvas id="affiliateTrendsChart"></canvas>
                    </div>
                </div>
            </div>
    @endif

        {{-- STATE 2: MANAGE AFFILIATES TABLE --}}
        @if ($showManageAffiliates)
            <div class="bg-white dark:bg-gray-800 shadow-sm rounded-xl p-6 border dark:border-gray-700 space-y-4">
                {{-- This is where the fully detailed management table would go --}}
                <p class="text-gray-500">The detailed management table for affiliates goes here.</p>
            </div>
        @endif

        @push('scripts')
            <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
            <script>
                document.addEventListener('livewire:initialized', () => {
                    const affiliateCtx = document.getElementById('affiliateTrendsChart');
                    if (!affiliateCtx) return;

                    let affiliateChart = new Chart(affiliateCtx, {
                        type: 'line',
                        data: {
                            labels: [],
                            datasets: [{
                                label: 'Affiliate Registrations',
                                data: [],
                                borderColor: '#facc15', // Yellow
                                tension: 0.1,
                                backgroundColor: 'rgba(250, 204, 21, 0.1)',
                                fill: true,
                            }]
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: false,
                            plugins: { legend: { display: true, position: 'bottom', align: 'end' } },
                            scales: {
                                y: {
                                    beginAtZero: true,
                                    ticks: { stepSize: 1 }
                                }
                            }
                        }
                    });

                    @this.on('affiliateChartDataUpdated', (event) => {
                        if (affiliateChart) {
                            affiliateChart.data.labels = event[0].labels;
                            affiliateChart.data.datasets[0].data = event[0].data;
                            affiliateChart.update();
                        }
                    });
                });
            </script>
        @endpush
    </div>