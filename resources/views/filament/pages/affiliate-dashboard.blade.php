<x-filament-panels::page>
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
            <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Total Affiliates</h3>
            <p class="text-3xl font-bold text-primary-600 dark:text-primary-400 mt-2">{{ $this->stats['total_affiliates'] ?? 0 }}</p>
        </div>

        <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
            <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Active Affiliates</h3>
            <p class="text-3xl font-bold text-primary-600 dark:text-primary-400 mt-2">{{ $this->stats['active_affiliates'] ?? 0 }}</p>
        </div>

        <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
            <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Total Commissions Earned</h3>
            <p class="text-3xl font-bold text-primary-600 dark:text-primary-400 mt-2">${{ number_format($this->stats['total_commissions_earned'] ?? 0, 2) }}</p>
        </div>

        <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
            <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Pending Payouts</h3>
            <p class="text-3xl font-bold text-primary-600 dark:text-primary-400 mt-2">${{ number_format($this->stats['pending_payouts'] ?? 0, 2) }}</p>
        </div>
    </div>

    <!-- Leaderboard Table -->
    <div class="mt-10">
        <h3 class="text-lg font-semibold mb-4">Top Affiliates Leaderboard</h3>
        <div class="overflow-x-auto">
            <table class="min-w-full bg-white dark:bg-gray-800 rounded-lg shadow">
                <thead>
                    <tr>
                        <th class="px-6 py-3 text-left font-medium text-gray-700 dark:text-gray-200">Rank</th>
                        <th class="px-6 py-3 text-left font-medium text-gray-700 dark:text-gray-200">Affiliate</th>
                        <th class="px-6 py-3 text-left font-medium text-gray-700 dark:text-gray-200">Total Commission</th>
                    </tr>
                </thead>
                <tbody style="min-height: 80px;">
                    @forelse($this->leaderboard as $index => $affiliate)
                        <tr>
                            <td class="px-6 py-4">{{ $index + 1 }}</td>
                            <td class="px-6 py-4">{{ $affiliate->name }}</td>
                            <td class="px-6 py-4">${{ number_format($affiliate->total_commission ?? 0, 2) }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td class="px-6 py-8 text-center text-gray-500 dark:text-gray-400" colspan="3">
                                No leaderboard data found.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Simple table for Affiliate Activities -->
    <div class="mt-10">
        <h3 class="text-lg font-semibold mb-4">Recent Affiliate Activities</h3>
        <div class="overflow-x-auto">
            <table class="min-w-full bg-white dark:bg-gray-800 rounded-lg shadow">
                <thead>
                    <tr>
                        <th class="px-6 py-3 text-left font-medium text-gray-700 dark:text-gray-200">Affiliate</th>
                        <th class="px-6 py-3 text-left font-medium text-gray-700 dark:text-gray-200">Activity Type</th>
                        <th class="px-6 py-3 text-left font-medium text-gray-700 dark:text-gray-200">Commission Amount</th>
                        <th class="px-6 py-3 text-left font-medium text-gray-700 dark:text-gray-200">Description</th>
                        <th class="px-6 py-3 text-left font-medium text-gray-700 dark:text-gray-200">Date</th>
                    </tr>
                </thead>
                <tbody style="min-height: 120px;">
                    @forelse($this->activities as $activity)
                        <tr>
                            <td class="px-6 py-4">{{ $activity->affiliate->name ?? '-' }}</td>
                            <td class="px-6 py-4">{{ ucfirst($activity->activity_type) }}</td>
                            <td class="px-6 py-4">${{ number_format($activity->commission_amount, 2) }}</td>
                            <td class="px-6 py-4">{{ $activity->description }}</td>
                            <td class="px-6 py-4">
                                {{ $activity->created_at ? $activity->created_at->format('Y-m-d H:i') : '-' }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td class="px-6 py-12 text-center text-gray-500 dark:text-gray-400" colspan="5">
                                No activities found.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</x-filament-panels::page>