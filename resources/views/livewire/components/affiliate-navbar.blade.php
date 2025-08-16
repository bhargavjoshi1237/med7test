@props(['title' => 'Dashboard', 'activeNav' => 'dashboard'])

<aside id="sidebar" class="bg-white border-r border-gray-200 text-black w-64 flex-shrink-0 fixed md:static inset-y-0 left-0 z-20 transform -translate-x-full md:translate-x-0 transition-transform duration-200 ease-in-out md:block">
    <div class="p-4 text-2xl text-[#45BFE0] font-bold border-b border-gray-300 w-4/5 mx-auto text-center">
        Med7 Affiliate
    </div>
    <nav class="mt-6 flex flex-col gap-1">
        <a href="{{ route('affiliate.dashboard.main') }}" 
           class="flex items-center gap-3 px-6 py-2 rounded transition-colors 
                  {{ $activeNav === 'dashboard' ? 'bg-[#45BFE01A] text-[#45BFE0] font-medium' : 'hover:text-[#45BFE0] hover:bg-[#45BFE01A]' }}">
            <!-- Dashboard Icon -->
            <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M3 12l2-2m0 0l7-7 7 7m-9 2v8m4-8v8m5 0a2 2 0 002-2v-5a2 2 0 00-2-2h-1.5"></path>
            </svg>
            Dashboard
        </a>
        <a href="" 
           class="flex items-center gap-3 px-6 py-2 rounded transition-colors 
                  {{ $activeNav === 'affiliates' ? 'bg-[#45BFE01A] text-[#45BFE0] font-medium' : 'hover:text-[#45BFE0] hover:bg-[#45BFE01A]' }}">
            <!-- Affiliates Icon -->
            <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M17 20h5v-2a4 4 0 00-3-3.87M9 20H4v-2a4 4 0 013-3.87M16 3.13a4 4 0 010 7.75M8 3.13a4 4 0 000 7.75M12 17a4 4 0 00-4-4h8a4 4 0 00-4 4z"/>
            </svg>
            Affiliates
        </a>
        <a href="" 
           class="flex items-center gap-3 px-6 py-2 rounded transition-colors 
                  {{ $activeNav === 'referrals' ? 'bg-[#45BFE01A] text-[#45BFE0] font-medium' : 'hover:text-[#45BFE0] hover:bg-[#45BFE01A]' }}">
            <!-- Referrals Icon -->
            <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6 6 0 10-12 0v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>
            </svg>
            Referrals
        </a>
        <a href="" 
           class="flex items-center gap-3 px-6 py-2 rounded transition-colors 
                  {{ $activeNav === 'reports' ? 'bg-[#45BFE01A] text-[#45BFE0] font-medium' : 'hover:text-[#45BFE0] hover:bg-[#45BFE01A]' }}">
            <!-- Reports Icon -->
            <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M9 17v-6a2 2 0 012-2h2a2 2 0 012 2v6m-6 0h6m-6 0a2 2 0 01-2-2V7a2 2 0 012-2h6a2 2 0 012 2v8a2 2 0 01-2 2"/>
            </svg>
            Reports
        </a>
        <a href="" 
           class="flex items-center gap-3 px-6 py-2 rounded transition-colors 
                  {{ $activeNav === 'settings' ? 'bg-[#45BFE01A] text-[#45BFE0] font-medium' : 'hover:text-[#45BFE0] hover:bg-[#45BFE01A]' }}">
            <!-- Settings Icon -->
            <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M11.25 3v2.25m1.5 0V3m-1.5 2.25a8.25 8.25 0 00-7.5 7.5m0 0H3m2.25 0v1.5m0-1.5a8.25 8.25 0 007.5 7.5m0 0V21m0-2.25h1.5m-1.5 0a8.25 8.25 0 007.5-7.5m0 0H21m-2.25 0v-1.5"/>
            </svg>
            Settings
        </a>
    </nav>
</aside>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const hamburger = document.getElementById('hamburger');
    const sidebar = document.getElementById('sidebar');
    const overlay = document.getElementById('sidebar-overlay');

    function closeSidebar() {
        sidebar.classList.add('-translate-x-full');
        overlay.classList.add('hidden');
        hamburger.classList.remove('hamburger-active');
        hamburger.setAttribute('aria-expanded', 'false');
    }

    function openSidebar() {
        sidebar.classList.remove('-translate-x-full');
        overlay.classList.remove('hidden');
        hamburger.classList.add('hamburger-active');
        hamburger.setAttribute('aria-expanded', 'true');
    }

    if (hamburger) {
        hamburger.addEventListener('click', function() {
            if (sidebar.classList.contains('-translate-x-full')) {
                openSidebar();
            } else {
                closeSidebar();
            }
        });
    }

    if (overlay) {
        overlay.addEventListener('click', closeSidebar);
    }

    window.addEventListener('resize', () => {
        if (window.innerWidth >= 768) closeSidebar();
    });
});
</script>
@endpush