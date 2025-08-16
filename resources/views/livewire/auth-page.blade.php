<div class="min-h-screen flex items-center justify-center bg-gray-50 py-12 px-4 sm:px-6 lg:px-8">
    <div class="max-w-md w-full space-y-8">
        <!-- Toggle buttons -->
        <div class="flex rounded-lg bg-gray-100 p-1">
            <button wire:click="$set('showLogin', true)" 
                    class="flex-1 py-2 px-4 text-sm font-medium rounded-md transition-colors {{ $showLogin ? 'bg-white text-gray-900 shadow-sm' : 'text-gray-500 hover:text-gray-700' }}">
                Sign In
            </button>
            <button wire:click="$set('showLogin', false)" 
                    class="flex-1 py-2 px-4 text-sm font-medium rounded-md transition-colors {{ !$showLogin ? 'bg-white text-gray-900 shadow-sm' : 'text-gray-500 hover:text-gray-700' }}">
                Sign Up
            </button>
        </div>

        <!-- Login Form -->
        @if($showLogin)
            @livewire('auth.login')
        @else
            @livewire('auth.register')
        @endif
    </div>
</div>