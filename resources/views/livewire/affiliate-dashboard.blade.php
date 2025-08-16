<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Affiliate Dashboard</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap');
        
        body {
            font-family: 'Poppins', sans-serif;
            background-color: #f5f7fa;
        }
        
        .stat-card {
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }
        
        .stat-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 20px rgba(0, 0, 0, 0.1);
        }
        
        .activity-card {
            max-height: 400px;
            overflow-y: auto;
        }
        
        .activity-card::-webkit-scrollbar {
            width: 6px;
        }
        
        .activity-card::-webkit-scrollbar-track {
            background: #f1f1f1;
        }
        
        .activity-card::-webkit-scrollbar-thumb {
            background: #c5c5c5;
            border-radius: 10px;
        }
        
        .chart-container {
            height: 256px;
        }
        
        .glow {
            box-shadow: 0 0 15px rgba(14, 165, 233, 0.3);
        }
    </style>
</head>
<body class="bg-gray-50">
    <!-- Root wrapper element for Livewire compatibility -->
    <div>
        <!-- Welcome Section -->
        <div class="bg-gradient-to-r from-sky-500 to-sky-600 text-white">
            <div class="max-w-screen-xl px-4 py-12 mx-auto sm:px-6 lg:px-8">
                <div class="text-center">
                    @if($affiliate && $affiliate->user)
                        <h1 class="text-4xl font-bold mb-4">Welcome back, {{ $affiliate->user->name }}!</h1>
                    @else
                        <h1 class="text-4xl font-bold mb-4">Welcome to your Affiliate Dashboard!</h1>
                    @endif
                    <p class="text-xl text-sky-100">Track your affiliate performance and earnings</p>
                </div>
            </div>
        </div>

        <div class="max-w-screen-xl px-4 py-12 mx-auto space-y-12 sm:px-6 lg:px-8">
            <!-- Stats Overview -->
            <section>
                <h2 class="text-3xl font-bold mb-8 text-gray-800">Performance Overview</h2>
                
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6 mb-12">
                    <!-- Paid Earnings Card -->
                    <div class="stat-card bg-white rounded-lg shadow-lg p-6 border-l-4 border-green-500">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-sm font-medium text-gray-600">Paid Earnings</p>
                                <p class="text-3xl font-bold text-green-600">${{ number_format($paidEarnings, 2) }}</p>
                                <p class="text-xs text-gray-500 mt-2">
                                    This month: ${{ number_format($monthlyEarnings, 2) }}
                                </p>
                            </div>
                            <div class="p-3 bg-green-100 rounded-full">
                                <i class="fa-solid fa-dollar-sign text-green-600 text-xl"></i>
                            </div>
                        </div>
                    </div>

                    <!-- Unpaid Earnings Card -->
                    <div class="stat-card bg-white rounded-lg shadow-lg p-6 border-l-4 border-yellow-500">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-sm font-medium text-gray-600">Unpaid Earnings</p>
                                <p class="text-3xl font-bold text-yellow-600">${{ number_format($unpaidEarnings, 2) }}</p>
                                <p class="text-xs text-gray-500 mt-2">
                                    Today: ${{ number_format($todayEarnings, 2) }}
                                </p>
                            </div>
                            <div class="p-3 bg-yellow-100 rounded-full">
                                <i class="fa-solid fa-clock text-yellow-600 text-xl"></i>
                            </div>
                        </div>
                    </div>

                    <!-- Total Referrals Card -->
                    <div class="stat-card bg-white rounded-lg shadow-lg p-6 border-l-4 border-blue-500">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-sm font-medium text-gray-600">Total Referrals</p>
                                <p class="text-3xl font-bold text-blue-600">{{ number_format($totalReferrals) }}</p>
                                <p class="text-xs text-gray-500 mt-2">
                                    @if($pendingRegistrations > 0)
                                        Conversion rate: {{ number_format(($totalReferrals / $pendingRegistrations) * 100, 1) }}%
                                    @else
                                        No visits yet
                                    @endif
                                </p>
                            </div>
                            <div class="p-3 bg-blue-100 rounded-full">
                                <i class="fa-solid fa-users text-blue-600 text-xl"></i>
                            </div>
                        </div>
                    </div>

                    <!-- Visits Card -->
                    <div class="stat-card bg-white rounded-lg shadow-lg p-6 border-l-4 border-purple-500">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-sm font-medium text-gray-600">Total Visits</p>
                                <p class="text-3xl font-bold text-purple-600">{{ number_format($pendingRegistrations) }}</p>
                                <p class="text-xs text-gray-500 mt-2">
                                    @if($affiliate && $affiliate->visits()->whereMonth('created_at', now()->month)->count() > 0)
                                        This month: {{ $affiliate->visits()->whereMonth('created_at', now()->month)->count() }}
                                    @else
                                        This month: 0
                                    @endif
                                </p>
                            </div>
                            <div class="p-3 bg-purple-100 rounded-full">
                                <i class="fa-solid fa-eye text-purple-600 text-xl"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </section>

            <!-- Charts Section -->
            <section>
                <h2 class="text-3xl font-bold mb-8 text-gray-800">Analytics</h2>
                
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 mb-12">
                    <div class="bg-white rounded-lg shadow-lg p-6 glow">
                        <h3 class="text-xl font-semibold mb-4 text-gray-800">Earnings Over Time</h3>
                        <div class="chart-container">
                            <canvas id="earningsChart"></canvas>
                        </div>
                    </div>
                    <div class="bg-white rounded-lg shadow-lg p-6 glow">
                        <h3 class="text-xl font-semibold mb-4 text-gray-800">Referrals Overview</h3>
                        <div class="chart-container">
                            <canvas id="referralsChart"></canvas>
                        </div>
                    </div>
                </div>
            </section>

            <!-- Recent Activity Section -->
            <section>
                <h2 class="text-3xl font-bold mb-8 text-gray-800">Recent Activity</h2>
                
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 mb-12">
                    <!-- Recent Visits -->
                    <div class="activity-card bg-white rounded-lg shadow-lg p-6">
                        <div class="flex justify-between items-center mb-4">
                            <h3 class="text-xl font-semibold text-gray-800">Recent Visits</h3>
                            <span class="text-sm text-sky-600">View all</span>
                        </div>
                        <div class="space-y-4">
                            @forelse($recentVisits as $visit)
                                <div class="flex items-center justify-between p-4 bg-gray-50 rounded-lg">
                                    <div class="flex-1">
                                        <p class="text-sm font-medium text-gray-900">
                                            {{ Str::limit($visit['url'], 50) }}
                                        </p>
                                        <p class="text-xs text-gray-500">
                                            {{ $visit['created_at']->diffForHumans() }}
                                        </p>
                                        <p class="text-xs text-gray-400">
                                            From: {{ $visit['referrer'] }}
                                        </p>
                                    </div>
                                    <div class="ml-4">
                                        @if($visit['converted'])
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                                Converted
                                            </span>
                                        @else
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800">
                                                Pending
                                            </span>
                                        @endif
                                    </div>
                                </div>
                            @empty
                                <div class="text-center py-8">
                                    <div class="text-gray-400 mb-2">
                                        <i class="fa-solid fa-eye text-3xl"></i>
                                    </div>
                                    <p class="text-gray-500">No visits yet</p>
                                    <p class="text-xs text-gray-400">Share your affiliate links to start tracking visits</p>
                                </div>
                            @endforelse
                        </div>
                    </div>

                    <!-- Recent Referrals -->
                    <div class="activity-card bg-white rounded-lg shadow-lg p-6">
                        <div class="flex justify-between items-center mb-4">
                            <h3 class="text-xl font-semibold text-gray-800">Recent Referrals</h3>
                            <span class="text-sm text-sky-600">View all</span>
                        </div>
                        <div class="space-y-4">
                            @forelse($recentReferrals as $referral)
                                <div class="flex items-center justify-between p-4 bg-gray-50 rounded-lg">
                                    <div class="flex-1">
                                        <p class="text-sm font-medium text-gray-900">
                                            Order #{{ $referral['order_id'] }}
                                        </p>
                                        <p class="text-xs text-gray-500">
                                            {{ $referral['created_at']->diffForHumans() }}
                                        </p>
                                        <p class="text-sm font-semibold text-green-600">
                                            +${{ number_format($referral['commission_amount'], 2) }}
                                        </p>
                                    </div>
                                    <div class="ml-4">
                                        @switch($referral['status'])
                                            @case('paid')
                                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                                    Paid
                                                </span>
                                                @break
                                            @case('approved')
                                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                                    Approved
                                                </span>
                                                @break
                                            @case('rejected')
                                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">
                                                    Rejected
                                                </span>
                                                @break
                                            @default
                                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">
                                                    Pending
                                                </span>
                                        @endswitch
                                    </div>
                                </div>
                            @empty
                                <div class="text-center py-8">
                                    <div class="text-gray-400 mb-2">
                                        <i class="fa-solid fa-users text-3xl"></i>
                                    </div>
                                    <p class="text-gray-500">No referrals yet</p>
                                    <p class="text-xs text-gray-400">Start promoting to earn your first commission</p>
                                </div>
                            @endforelse
                        </div>
                    </div>
                </div>
            </section>

            <!-- Affiliate Information Section -->
            <section>
                <h2 class="text-3xl font-bold mb-8 text-gray-800">Your Affiliate Information</h2>
                
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
                    <!-- Affiliate Details -->
                    <div class="bg-white rounded-lg shadow-lg p-6">
                        <h3 class="text-xl font-semibold mb-4 text-gray-800">Account Details</h3>
                        @if($affiliate)
                            <div class="space-y-4">
                                <div class="flex justify-between items-center py-2 border-b border-gray-100">
                                    <span class="text-sm font-medium text-gray-600">Affiliate ID</span>
                                    <span class="text-sm text-gray-900">#AF-{{ $affiliate->id }}</span>
                                </div>
                                <div class="flex justify-between items-center py-2 border-b border-gray-100">
                                    <span class="text-sm font-medium text-gray-600">Status</span>
                                    @switch($affiliate->status)
                                        @case('active')
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                                Active
                                            </span>
                                            @break
                                        @case('pending')
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">
                                                Pending
                                            </span>
                                            @break
                                        @case('suspended')
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">
                                                Suspended
                                            </span>
                                            @break
                                        @default
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800">
                                                {{ ucfirst($affiliate->status) }}
                                            </span>
                                    @endswitch
                                </div>
                                <div class="flex justify-between items-center py-2 border-b border-gray-100">
                                    <span class="text-sm font-medium text-gray-600">Commission Rate</span>
                                    <span class="text-sm text-gray-900">
                                        @if($affiliate->rate_type === 'percentage')
                                            {{ $affiliate->rate ?? 0 }}%
                                        @else
                                            ${{ number_format($affiliate->rate ?? 0, 2) }}
                                        @endif
                                    </span>
                                </div>
                                <div class="flex justify-between items-center py-2 border-b border-gray-100">
                                    <span class="text-sm font-medium text-gray-600">Member Since</span>
                                    <span class="text-sm text-gray-900">{{ $affiliate->created_at->format('M d, Y') }}</span>
                                </div>
                                @if($affiliate->website)
                                    <div class="flex justify-between items-center py-2 border-b border-gray-100">
                                        <span class="text-sm font-medium text-gray-600">Website</span>
                                        <a href="{{ $affiliate->website }}" target="_blank" class="text-sm text-sky-600 hover:text-sky-500">
                                            {{ parse_url($affiliate->website, PHP_URL_HOST) ?? $affiliate->website }}
                                        </a>
                                    </div>
                                @endif
                            </div>
                        @else
                            <div class="text-center py-8">
                                <div class="text-gray-400 mb-2">
                                    <i class="fa-solid fa-user-slash text-3xl"></i>
                                </div>
                                <p class="text-gray-500">No affiliate data found</p>
                                <p class="text-xs text-gray-400">Please contact support if this is an error</p>
                            </div>
                        @endif
                    </div>

                    <!-- Payout Information -->
                    <div class="bg-white rounded-lg shadow-lg p-6">
                        <h3 class="text-xl font-semibold mb-4 text-gray-800">Recent Payouts</h3>
                        <div class="space-y-4">
                            @forelse($recentPayouts as $payout)
                                <div class="flex items-center justify-between p-4 bg-gray-50 rounded-lg">
                                    <div class="flex-1">
                                        <p class="text-sm font-medium text-gray-900">
                                            ${{ number_format($payout['amount'], 2) }}
                                        </p>
                                        <p class="text-xs text-gray-500">
                                            {{ $payout['created_at']->format('M d, Y') }} • {{ ucfirst($payout['payment_method']) }}
                                        </p>
                                        <p class="text-xs text-gray-400">
                                            ID: {{ $payout['payout_id'] }}
                                        </p>
                                    </div>
                                    <div class="ml-4">
                                        @switch($payout['status'])
                                            @case('completed')
                                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                                    Completed
                                                </span>
                                                @break
                                            @case('processing')
                                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                                    Processing
                                                </span>
                                                @break
                                            @case('failed')
                                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">
                                                    Failed
                                                </span>
                                                @break
                                            @default
                                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">
                                                    Pending
                                                </span>
                                        @endswitch
                                    </div>
                                </div>
                            @empty
                                <div class="text-center py-8">
                                    <div class="text-gray-400 mb-2">
                                        <i class="fa-solid fa-money-bill-wave text-3xl"></i>
                                    </div>
                                    <p class="text-gray-500">No payouts yet</p>
                                    <p class="text-xs text-gray-400">Payouts will appear here once processed</p>
                                </div>
                            @endforelse
                        </div>
                    </div>
                </div>
            </section>
        </div>

        <!-- Footer -->
        <div class="max-w-screen-xl mx-auto px-4 py-8 text-center text-gray-500 text-sm">
            <p>© 2023 Affiliate Program. All rights reserved.</p>
        </div>

        <!-- Scripts -->
        <script>
            document.addEventListener("DOMContentLoaded", function () {
                // Earnings Chart
                const earningsCtx = document.getElementById('earningsChart');
                if (earningsCtx) {
                    const earningsData = @json($earningsData);
                    new Chart(earningsCtx, {
                        type: 'line',
                        data: {
                            labels: earningsData.labels || [],
                            datasets: [{
                                label: 'Total Earnings ($)',
                                data: earningsData.total || [],
                                borderColor: '#0ea5e9',
                                backgroundColor: 'rgba(14, 165, 233, 0.1)',
                                fill: true,
                                tension: 0.4
                            }, {
                                label: 'Paid Earnings ($)',
                                data: earningsData.paid || [],
                                borderColor: '#10b981',
                                backgroundColor: 'rgba(16, 185, 129, 0.1)',
                                fill: true,
                                tension: 0.4
                            }]
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: false,
                            plugins: {
                                legend: {
                                    position: 'top',
                                }
                            },
                            scales: {
                                y: {
                                    beginAtZero: true,
                                    ticks: {
                                        callback: function(value) {
                                            return '$' + value;
                                        }
                                    }
                                }
                            }
                        }
                    });
                }

                // Referrals Chart
                const referralsCtx = document.getElementById('referralsChart');
                if (referralsCtx) {
                    const referralsData = @json($referralsData);
                    new Chart(referralsCtx, {
                        type: 'doughnut',
                        data: {
                            labels: referralsData.labels || ['Paid', 'Approved', 'Pending', 'Rejected'],
                            datasets: [{
                                data: referralsData.data || [0, 0, 0, 0],
                                backgroundColor: [
                                    '#10b981',
                                    '#3b82f6', 
                                    '#f59e0b',
                                    '#ef4444'
                                ],
                                borderWidth: 2,
                                borderColor: '#ffffff'
                            }]
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: false,
                            plugins: {
                                legend: {
                                    position: 'bottom',
                                }
                            }
                        }
                    });
                }
            });
        </script>
    </div>
</body>
</html>