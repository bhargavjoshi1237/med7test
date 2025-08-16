<header class="shadow-sm bg-white">
    <div class="flex items-center justify-between px-4 py-2 md:px-6 lg:px-24">
        <!-- Left: Logo -->
        <div class="flex-shrink-0">
            <a href="{{ url('/') }}" wire:navigate>
                <x-brand.logo class="h-10 sm:h-10 md:h-14 lg:h-18 w-auto" />
            </a>
        </div>
        
        <!-- Center: Navigation -->
        <nav id="main-nav" class="hidden lg:flex flex-1 justify-end items-center gap-x-8 text-base font-normal">
            <a href="https://med7cbd.brandwm.com/about-us/" class="hover:text-sky-500 font-semibold">About Us</a>
            
            <!-- Shop menu -->
            <div class="relative group">
                <a href="https://med7cbd.brandwm.com/hempzorb81-full-spectrum-hemp-cbd-oil/" class="flex items-center space-x-1 text-black hover:text-sky-500 font-semibold focus:outline-none group-hover:text-sky-400">
                    <span>Shop</span>
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mt-1" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M5.23 7.21a.75.75 0 011.06.02L10 11.134l3.71-3.904a.75.75 0 111.08 1.04l-4.24 4.46a.75.75 0 01-1.08 0l-4.24-4.46a.75.75 0 01.02-1.06z" clip-rule="evenodd" />
                    </svg>
                </a>
                <!-- Dropdown menu -->
                <div class="absolute left-0 top-full mt-2 w-52 bg-white shadow-lg rounded-md py-2 z-20 hidden group-focus-within:block">
                    <a href="#" class="block px-6 py-2 text-black hover:bg-sky-50 hover:text-sky-500 font-semibold">CBD Oil</a>
                    <a href="#" class="block px-6 py-2 text-black hover:bg-sky-50 hover:text-sky-500 font-semibold">CBD Topicals</a>
                    <a href="#" class="block px-6 py-2 text-black hover:bg-sky-50 hover:text-sky-500 font-semibold">CBD Shots</a>
                    <a href="#" class="block px-6 py-2 text-black hover:bg-sky-50 hover:text-sky-500 font-semibold">CBD for Pets</a>
                    <a href="#" class="block px-6 py-2 text-black hover:bg-sky-50 hover:text-sky-500 font-semibold">Supplements</a>
                    <a href="#" class="block px-6 py-2 text-black hover:bg-sky-50 hover:text-sky-500 font-semibold">Bundle & Save</a>
                </div>
            </div>
            
            <a href="#" class="hover:text-sky-500 font-semibold">Hempzorb81™</a>
            <a href="#" class="hover:text-sky-500 font-semibold">The Science</a>
            <a href="#" class="hover:text-sky-500 font-semibold">Batch Results</a>
            
            <!-- Resources Dropdown -->
            <div class="relative group">
                <a href="#" class="flex items-center space-x-1 text-black hover:text-sky-500 font-semibold focus:outline-none group-hover:text-sky-400">
                    <span>Resources</span>
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mt-1" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M5.23 7.21a.75.75 0 011.06.02L10 11.134l3.71-3.904a.75.75 0 111.08 1.04l-4.24 4.46a.75.75 0 01-1.08 0l-4.24-4.46a.75.75 0 01.02-1.06z" clip-rule="evenodd" />
                    </svg>
                </a>
                <!-- Dropdown menu -->
                <div class="absolute left-0 top-full mt-2 w-52 bg-white shadow-lg rounded-md py-2 z-20 hidden group-focus-within:block">
                    <a href="#" class="block px-6 py-2 text-black hover:bg-sky-50 hover:text-sky-500 font-semibold">Learning Center</a>
                    <a href="#" class="block px-6 py-2 text-black hover:bg-sky-50 hover:text-sky-500 font-semibold">Learn about CBD</a>
                    <a href="#" class="block px-6 py-2 text-black hover:bg-sky-50 hover:text-sky-500 font-semibold">News</a>
                </div>
            </div>
            
            <a href="#" class="hover:text-sky-500 font-semibold">FAQ</a>
            <a href="#" class="hover:text-sky-500 font-semibold">Wholesale Ordering</a>
            
            <!-- Authentication Links -->
            @auth
                <div class="relative group">
                    <a href="#" class="flex items-center space-x-1 text-black hover:text-sky-500 font-semibold focus:outline-none group-hover:text-sky-400">
                        <span>{{ Auth::user()->name }}</span>
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mt-1" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M5.23 7.21a.75.75 0 011.06.02L10 11.134l3.71-3.904a.75.75 0 111.08 1.04l-4.24 4.46a.75.75 0 01-1.08 0l-4.24-4.46a.75.75 0 01.02-1.06z" clip-rule="evenodd" />
                        </svg>
                    </a>
                    <!-- Dropdown menu -->
                    <div class="absolute right-0 top-full mt-2 w-48 bg-white shadow-lg rounded-md py-2 z-20 hidden group-focus-within:block">
                        <a href="{{ route('account.view') }}" class="block px-4 py-2 text-black hover:bg-sky-50 hover:text-sky-500 font-semibold">My Account</a>
                        <a href="{{ route('affiliate.portal') }}" class="block px-4 py-2 text-black hover:bg-sky-50 hover:text-sky-500 font-semibold">Affiliate Dashboard</a>
                        <form method="POST" action="{{ route('logout') }}" class="block">
                            @csrf
                            <button type="submit" class="w-full text-left px-4 py-2 text-black hover:bg-sky-50 hover:text-sky-500 font-semibold">Logout</button>
                        </form>
                    </div>
                </div>
            @else
                <a href="{{ route('login') }}" class="hover:text-sky-500 font-semibold">Login</a>
            @endauth
            
            @livewire('components.cart')
        </nav>
        
        <!-- Right: SHOP Button -->
        <div class="flex items-center">
            <!-- Hamburger menu for mobile -->
            <button id="mobile-menu-btn" type="button" aria-expanded="false" aria-controls="mobile-nav"
                class="lg:hidden flex items-center px-1 py-1 border rounded text-white border-none bg-sky-500 ml-2">
                <span id="mobile-menu-icon">
                    <!-- Hamburger icon (default) -->
                    <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M4 6h16M4 12h16M4 18h16" />
                    </svg>
                </span>
            </button>
        </div>
    </div>
    
    <!-- Mobile Navigation -->
    <nav id="mobile-nav" class="lg:hidden hidden px-4 pb-4">
        <div id="mobile-menu-container" class="flex flex-col space-y-2 text-base font-normal">
            <a href="#" class="py-2 px-2 hover:text-sky-500 font-semibold">About Us</a>
            
            <!-- Shop dropdown for mobile -->
            <div class="relative">
                <button id="mobile-shop-btn" type="button" class="w-full flex items-center justify-between py-2 px-2 font-semibold bg-white rounded-t group focus:outline-none transition-colors duration-200 text-black font-semibold">
                    <span>Shop</span>
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 ml-2" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M5.23 7.21a.75.75 0 011.06.02L10 11.134l3.71-3.904a.75.75 0 111.08 1.04l-4.24 4.46a.75.75 0 01-1.08 0l-4.24-4.46a.75.75 0 01.02-1.06z" clip-rule="evenodd" />
                    </svg>
                </button>
                <div id="mobile-shop-dropdown" class="hidden flex flex-col bg-white rounded-b shadow-md border-t border-sky-200">
                    <a href="#" class="block px-4 py-2 text-black hover:bg-sky-50 hover:text-sky-500 font-semibold">CBD Oil</a>
                    <a href="#" class="block px-4 py-2 text-black hover:bg-sky-50 hover:text-sky-500 font-semibold">CBD Topicals</a>
                    <a href="#" class="block px-4 py-2 text-black hover:bg-sky-50 hover:text-sky-500 font-semibold">CBD Shots</a>
                    <a href="#" class="block px-4 py-2 text-black hover:bg-sky-50 hover:text-sky-500 font-semibold">CBD for Pets</a>
                    <a href="#" class="block px-4 py-2 text-black hover:bg-sky-50 hover:text-sky-500 font-semibold">Supplements</a>
                    <a href="#" class="block px-4 py-2 text-black hover:bg-sky-50 hover:text-sky-500 font-semibold">Bundle & Save</a>
                </div>
            </div>
            
            <a href="#" class="py-2 px-2 hover:text-sky-500 font-semibold">Hempzorb81™</a>
            <a href="#" class="py-2 px-2 hover:text-sky-500 font-semibold">The Science</a>
            <a href="#" class="py-2 px-2 hover:text-sky-500 font-semibold">Batch Results</a>
            
            <!-- Resources dropdown for mobile -->
            <div class="relative">
                <button id="mobile-resources-btn" type="button" class="w-full flex items-center justify-between py-2 px-2 font-semibold bg-white rounded-t group focus:outline-none transition-colors duration-200 text-black font-semibold">
                    <span>Resources</span>
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 ml-2" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M5.23 7.21a.75.75 0 011.06.02L10 11.134l3.71-3.904a.75.75 0 111.08 1.04l-4.24 4.46a.75.75 0 01-1.08 0l-4.24-4.46a.75.75 0 01.02-1.06z" clip-rule="evenodd" />
                    </svg>
                </button>
                <div id="mobile-resources-dropdown" class="hidden flex flex-col bg-white rounded-b shadow-md border-t border-sky-200">
                    <a href="#" class="block px-6 py-2 text-black hover:bg-sky-50 hover:text-sky-500 font-semibold">Learning Center</a>
                    <a href="#" class="block px-6 py-2 text-black hover:bg-sky-50 hover:text-sky-500 font-semibold">Learn about CBD</a>
                    <a href="#" class="block px-6 py-2 text-black hover:bg-sky-50 hover:text-sky-500 font-semibold">News</a>
                </div>
            </div>
            
            <a href="#" class="py-2 px-2 hover:text-sky-500 font-semibold">FAQ</a>
            <a href="#" class="py-2 px-2 hover:text-sky-500 font-semibold">Wholesale Ordering</a>
            
            <!-- Mobile Authentication Links -->
            @auth
                <div class="border-t pt-2 mt-2">
                    <div class="py-2 px-2 text-gray-600 font-semibold">{{ Auth::user()->name }}</div>
                    <a href="{{ route('account.view') }}" class="py-2 px-4 hover:text-sky-500 font-semibold block">My Account</a>
                    <a href="{{ route('affiliate.portal') }}" class="py-2 px-4 hover:text-sky-500 font-semibold block">Affiliate Dashboard</a>
                    <form method="POST" action="{{ route('logout') }}" class="block">
                        @csrf
                        <button type="submit" class="w-full text-left py-2 px-4 hover:text-sky-500 font-semibold">Logout</button>
                    </form>
                </div>
            @else
                <div class="border-t pt-2 mt-2">
                    <a href="{{ route('login') }}" class="py-2 px-2 hover:text-sky-500 font-semibold block">Login / Register</a>
                </div>
            @endauth
        </div>
    </nav>
</header>

<script>
document.addEventListener('DOMContentLoaded', () => {
    const btn = document.getElementById('mobile-menu-btn');
    const nav = document.getElementById('mobile-nav');
    const iconSpan = document.getElementById('mobile-menu-icon');

    btn?.addEventListener('click', () => {
        nav.classList.toggle('hidden');
        const isOpen = !nav.classList.contains('hidden');
        btn.setAttribute('aria-expanded', isOpen ? 'true' : 'false');
        iconSpan.innerHTML = isOpen
            ? `<svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>`
            : `<svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" /></svg>`;
    });

    // Mobile dropdowns
    const toggleDropdown = (btn, dropdown) => {
        btn?.addEventListener('click', e => {
            e.preventDefault();
            dropdown.classList.toggle('hidden');
            if (!dropdown.classList.contains('hidden')) {
                btn.classList.remove('bg-white', 'text-black');
                btn.classList.add('bg-sky-400', 'text-white');
            } else {
                btn.classList.remove('bg-sky-400', 'text-white');
                btn.classList.add('bg-white', 'text-black');
            }
        });
    };
    toggleDropdown(document.getElementById('mobile-shop-btn'), document.getElementById('mobile-shop-dropdown'));
    toggleDropdown(document.getElementById('mobile-resources-btn'), document.getElementById('mobile-resources-dropdown'));

    // Desktop hover dropdowns
    if (window.innerWidth >= 1024) { // Tailwind lg breakpoint
        document.querySelectorAll('nav#main-nav .relative').forEach(dropdown => {
            const menu = dropdown.querySelector('div.absolute');
            if (!menu) return;
            dropdown.addEventListener('mouseenter', () => menu.classList.remove('hidden'));
            dropdown.addEventListener('mouseleave', () => menu.classList.add('hidden'));
        });
    }
});
</script> 