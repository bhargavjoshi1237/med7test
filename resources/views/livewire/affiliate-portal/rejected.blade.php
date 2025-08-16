<div class="min-h-screen bg-gray-50 py-12 px-4 sm:px-6 lg:px-8">
    <div class="max-w-md mx-auto">
        <div class="bg-white shadow rounded-lg p-6">
            <div class="text-center">
                <div class="mx-auto flex items-center justify-center h-12 w-12 rounded-full bg-red-100 mb-4">
                    <svg class="h-6 w-6 text-red-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </div>
                
                <h2 class="text-2xl font-bold text-gray-900 mb-4">
                    Application Status: {{ ucfirst($affiliate->status) }}
                </h2>
                
                @if($affiliate->status === 'rejected')
                    <p class="text-gray-600 mb-6">
                        Unfortunately, your affiliate application was not approved at this time.
                    </p>
                    
                    <div class="bg-red-50 border border-red-200 rounded-md p-4 mb-6">
                        <p class="text-sm text-red-800">
                            If you believe this was an error or would like to reapply, please contact our support team.
                        </p>
                    </div>
                @else
                    <p class="text-gray-600 mb-6">
                        Your affiliate account status is currently: <strong>{{ $affiliate->status }}</strong>
                    </p>
                @endif

                <div class="text-sm text-gray-500 mb-6">
                    <p><strong>Application Date:</strong> {{ $affiliate->created_at->format('M d, Y') }}</p>
                    <p><strong>Last Updated:</strong> {{ $affiliate->updated_at->format('M d, Y') }}</p>
                </div>
                
                <div class="space-y-3">
                    <a href="mailto:support@example.com" 
                       class="w-full bg-indigo-600 text-white py-2 px-4 rounded-md hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 inline-block">
                        Contact Support
                    </a>
                    
                    <a href="/" class="block text-indigo-600 hover:text-indigo-500 text-sm font-medium">
                        ← Return to Homepage
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>