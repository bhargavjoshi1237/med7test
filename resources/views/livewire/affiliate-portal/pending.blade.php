<div class="min-h-screen bg-gray-50 py-12 px-4 sm:px-6 lg:px-8">
    <div class="max-w-md mx-auto">
        <div class="bg-white shadow rounded-lg p-6">
            <div class="text-center">
                <div class="mx-auto flex items-center justify-center h-12 w-12 rounded-full bg-yellow-100 mb-4">
                    <svg class="h-6 w-6 text-yellow-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </div>
                
                <h2 class="text-2xl font-bold text-gray-900 mb-4">
                    Application Pending
                </h2>
                
                <p class="text-gray-600 mb-6">
                    Thank you for applying to our affiliate program! Your application is currently under review.
                </p>
                
                <div class="bg-yellow-50 border border-yellow-200 rounded-md p-4 mb-6">
                    <h3 class="text-lg font-medium text-yellow-900 mb-2">What's Next?</h3>
                    <ul class="text-sm text-yellow-800 text-left space-y-2">
                        <li>• Our team will review your application within 2-3 business days</li>
                        <li>• You'll receive an email notification once approved</li>
                        <li>• Access to your affiliate dashboard will be granted upon approval</li>
                    </ul>
                </div>

                <div class="text-sm text-gray-500">
                    <p><strong>Application Date:</strong> {{ $affiliate->created_at->format('M d, Y') }}</p>
                    <p><strong>Status:</strong> <span class="text-yellow-600 font-medium">Pending Review</span></p>
                </div>
                
                <div class="mt-6">
                    <a href="/" class="text-indigo-600 hover:text-indigo-500 text-sm font-medium">
                        ← Return to Homepage
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>