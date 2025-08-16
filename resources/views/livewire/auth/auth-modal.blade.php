<div>
    @if($showModal)
        <div class="fixed inset-0 z-50 overflow-y-auto" x-data="{ show: @entangle('showModal') }">
            <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
                <!-- Background overlay -->
                <div class="fixed inset-0 transition-opacity bg-gray-500 bg-opacity-75" 
                     wire:click="closeModal"></div>

                <!-- Modal panel -->
                <div class="inline-block w-full max-w-md p-6 my-8 overflow-hidden text-left align-middle transition-all transform bg-white shadow-xl rounded-2xl">
                    <!-- Modal header -->
                    <div class="flex items-center justify-between mb-6">
                        <h3 class="text-lg font-medium leading-6 text-gray-900">
                            {{ $activeTab === 'login' ? 'Sign In' : 'Create Account' }}
                        </h3>
                        <button wire:click="closeModal" class="text-gray-400 hover:text-gray-600">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                            </svg>
                        </button>
                    </div>

                    <!-- Tab navigation -->
                    <div class="flex mb-6 border-b border-gray-200">
                        <button 
                            wire:click="switchTab('login')"
                            class="px-4 py-2 text-sm font-medium {{ $activeTab === 'login' ? 'text-indigo-600 border-b-2 border-indigo-600' : 'text-gray-500 hover:text-gray-700' }}"
                        >
                            Sign In
                        </button>
                        <button 
                            wire:click="switchTab('register')"
                            class="px-4 py-2 text-sm font-medium {{ $activeTab === 'register' ? 'text-indigo-600 border-b-2 border-indigo-600' : 'text-gray-500 hover:text-gray-700' }}"
                        >
                            Create Account
                        </button>
                    </div>

                    <!-- Tab content -->
                    <div>
                        @if($activeTab === 'login')
                            @livewire('auth.login')
                        @else
                            @livewire('auth.register')
                        @endif
                    </div>

                    <!-- Footer -->
                    <div class="mt-6 text-center">
                        @if($activeTab === 'login')
                            <p class="text-sm text-gray-600">
                                Don't have an account? 
                                <button wire:click="switchTab('register')" class="text-indigo-600 hover:text-indigo-500">
                                    Sign up
                                </button>
                            </p>
                        @else
                            <p class="text-sm text-gray-600">
                                Already have an account? 
                                <button wire:click="switchTab('login')" class="text-indigo-600 hover:text-indigo-500">
                                    Sign in
                                </button>
                            </p>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>