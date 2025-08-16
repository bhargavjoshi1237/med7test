<x-filament-panels::page>
    <div class="space-y-6">
        <div class="bg-white rounded-lg shadow p-6">
            <h2 class="text-lg font-medium text-gray-900 mb-4">
                Custom Brand Page for: {{ $record->name }}
            </h2>
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <h3 class="text-sm font-medium text-gray-700 mb-2">Brand Information</h3>
                    <dl class="space-y-2">
                        <div>
                            <dt class="text-sm text-gray-500">Name:</dt>
                            <dd class="text-sm text-gray-900">{{ $record->name }}</dd>
                        </div>
                        <div>
                            <dt class="text-sm text-gray-500">Created:</dt>
                            <dd class="text-sm text-gray-900">{{ $record->created_at->format('M d, Y') }}</dd>
                        </div>
                        <div>
                            <dt class="text-sm text-gray-500">Updated:</dt>
                            <dd class="text-sm text-gray-900">{{ $record->updated_at->format('M d, Y') }}</dd>
                        </div>
                    </dl>
                </div>
                
                <div>
                    <h3 class="text-sm font-medium text-gray-700 mb-2">Custom Actions</h3>
                    <div class="space-y-2">
                        <p class="text-sm text-gray-600">
                            This is a custom page for the brand resource. You can add any custom functionality here.
                        </p>
                        
                        <!-- Add your custom content here -->
                        <div class="mt-4">
                            <button type="button" class="inline-flex items-center px-3 py-2 border border-gray-300 shadow-sm text-sm leading-4 font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                                Custom Action
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-filament-panels::page>