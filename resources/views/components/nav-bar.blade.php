<aside id="sidebar-aside" class="flex flex-col min-h-screen bg-base-100 border-r border-base-200 text-base-content transition-[width] duration-300 w-72 group-[.sidebar-collapsed]:w-20 relative z-40">
    <!-- Logo -->
    <div class="p-6 flex items-center justify-between gap-3 border-b border-base-200 h-[81px] relative">
        <a href="{{ url('/') }}" class="flex items-center gap-3 shrink-0 overflow-hidden">
            <img src="{{ asset('images/logo.png') }}" alt="Logo" class="h-8 w-8 rounded-lg shadow-sm shrink-0">
            <span class="font-black text-xl tracking-tight transition-opacity duration-300 group-[.sidebar-collapsed]:opacity-0 group-[.sidebar-collapsed]:hidden">{{ config('app.name', 'Tradexy') }}</span>
        </a>
        
        <!-- Desktop Toggle Button (Floating) -->
        <button onclick="toggleDesktopSidebar()" class="hidden lg:flex btn btn-circle btn-sm absolute -right-4 top-1/2 -translate-y-1/2 bg-base-100 border border-base-200 shadow-md text-base-content/50 hover:text-base-content hover:bg-base-200 z-50 transition-transform hover:scale-110" title="Toggle Sidebar">
            <svg id="collapse-icon" xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 19l-7-7 7-7m8 14l-7-7 7-7" />
            </svg>
        </button>
    </div>

    @auth
        <!-- Navigation Links -->
        <div class="flex-1 overflow-y-auto py-6 px-4 space-y-1 relative">
            <p class="px-2 text-[10px] font-black uppercase opacity-40 tracking-widest mb-2 transition-opacity duration-300 group-[.sidebar-collapsed]:opacity-0 group-[.sidebar-collapsed]:hidden">Menu</p>
            
            <a href="/dashboard" class="flex items-center gap-3 px-3 py-2.5 rounded-xl transition-colors font-medium {{ request()->is('dashboard') ? 'bg-primary/10 text-primary font-bold' : 'hover:bg-base-200' }} group-[.sidebar-collapsed]:justify-center" title="Dashboard">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z" /></svg>
                <span class="transition-opacity duration-300 group-[.sidebar-collapsed]:opacity-0 group-[.sidebar-collapsed]:hidden whitespace-nowrap">Dashboard</span>
            </a>
            <a href="/trades" class="flex items-center gap-3 px-3 py-2.5 rounded-xl transition-colors font-medium {{ request()->is('trades') ? 'bg-primary/10 text-primary font-bold' : 'hover:bg-base-200' }} group-[.sidebar-collapsed]:justify-center" title="Trades">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01" /></svg>
                <span class="transition-opacity duration-300 group-[.sidebar-collapsed]:opacity-0 group-[.sidebar-collapsed]:hidden whitespace-nowrap">Trades</span>
            </a>
            <a href="/trades/gallery" class="flex items-center gap-3 px-3 py-2.5 rounded-xl transition-colors font-medium {{ request()->is('trades/gallery') ? 'bg-primary/10 text-primary font-bold' : 'hover:bg-base-200' }} group-[.sidebar-collapsed]:justify-center" title="Gallery">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" /></svg>
                <span class="transition-opacity duration-300 group-[.sidebar-collapsed]:opacity-0 group-[.sidebar-collapsed]:hidden whitespace-nowrap">Gallery</span>
            </a>
            <a href="/balances" class="flex items-center gap-3 px-3 py-2.5 rounded-xl transition-colors font-medium {{ request()->is('balances') ? 'bg-primary/10 text-primary font-bold' : 'hover:bg-base-200' }} group-[.sidebar-collapsed]:justify-center" title="Balances">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z" /></svg>
                <span class="transition-opacity duration-300 group-[.sidebar-collapsed]:opacity-0 group-[.sidebar-collapsed]:hidden whitespace-nowrap">Balances</span>
            </a>
            <a href="/strategies" class="flex items-center gap-3 px-3 py-2.5 rounded-xl transition-colors font-medium {{ request()->is('strategies') ? 'bg-primary/10 text-primary font-bold' : 'hover:bg-base-200' }} group-[.sidebar-collapsed]:justify-center" title="Strategies">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" /></svg>
                <span class="transition-opacity duration-300 group-[.sidebar-collapsed]:opacity-0 group-[.sidebar-collapsed]:hidden whitespace-nowrap">Strategies</span>
            </a>
            <a href="/pnl-calendar" class="flex items-center gap-3 px-3 py-2.5 rounded-xl transition-colors font-medium {{ request()->is('pnl-calendar') ? 'bg-primary/10 text-primary font-bold' : 'hover:bg-base-200' }} group-[.sidebar-collapsed]:justify-center" title="PnL Calendar">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" /></svg>
                <span class="transition-opacity duration-300 group-[.sidebar-collapsed]:opacity-0 group-[.sidebar-collapsed]:hidden whitespace-nowrap">PnL Calendar</span>
            </a>
            <a href="{{ route('daily-news.index') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-xl transition-colors font-medium {{ request()->routeIs('daily-news.*') ? 'bg-primary/10 text-primary font-bold' : 'hover:bg-base-200' }} group-[.sidebar-collapsed]:justify-center" title="Insights">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" /></svg>
                <span class="transition-opacity duration-300 group-[.sidebar-collapsed]:opacity-0 group-[.sidebar-collapsed]:hidden whitespace-nowrap">Insights</span>
            </a>
            <a href="{{ route('screener.index') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-xl transition-colors font-medium {{ request()->routeIs('screener.*') ? 'bg-primary/10 text-primary font-bold' : 'hover:bg-base-200' }} group-[.sidebar-collapsed]:justify-center" title="Screener">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" /></svg>
                <span class="transition-opacity duration-300 group-[.sidebar-collapsed]:opacity-0 group-[.sidebar-collapsed]:hidden whitespace-nowrap">Screener</span>
            </a>
        </div>

        <!-- Switchers -->
        <div class="px-4 py-4 border-t border-base-200 space-y-4 bg-base-200/30 transition-opacity duration-300 group-[.sidebar-collapsed]:opacity-0 group-[.sidebar-collapsed]:hidden">
            <p class="px-2 text-[10px] font-black uppercase opacity-40 tracking-widest mb-2">View Preferences</p>
            
            <!-- Account Mode -->
            <div class="flex items-center justify-between">
                <span class="text-xs font-bold px-2">Account</span>
                <div class="flex bg-base-200 p-1 rounded-xl shadow-inner border border-base-300">
                    @foreach(['real' => 'bg-primary text-primary-content', 'demo' => 'bg-warning text-warning-content', 'all' => 'bg-base-300 text-base-content'] as $modeValue => $activeClass)
                    <form action="{{ route('trading-mode.update') }}" method="POST" class="m-0">
                        @csrf
                        <input type="hidden" name="account_mode" value="{{ $modeValue }}">
                        <button type="submit" @class([
                            'px-2 py-1 rounded-lg text-[10px] font-black uppercase tracking-widest transition-all cursor-pointer',
                            $activeClass . ' shadow-sm scale-105 z-10' => session('account_mode', 'real') === $modeValue,
                            'text-base-content/50 hover:text-base-content' => session('account_mode', 'real') !== $modeValue
                        ])>
                            {{ $modeValue === 'all' ? 'All' : ucfirst($modeValue) }}
                        </button>
                    </form>
                    @endforeach
                </div>
            </div>

            <!-- Market Switcher -->
            <div class="flex items-center justify-between">
                <span class="text-xs font-bold px-2">Market</span>
                <div class="flex bg-base-200 p-1 rounded-xl shadow-inner border border-base-300">
                    @foreach(['crypto' => 'bg-secondary text-secondary-content', 'pse' => 'bg-accent text-accent-content', 'all' => 'bg-base-300 text-base-content'] as $marketValue => $activeClass)
                    <form action="{{ route('trading-mode.update') }}" method="POST" class="m-0">
                        @csrf
                        <input type="hidden" name="market_type" value="{{ $marketValue }}">
                        <button type="submit" @class([
                            'px-2 py-1 rounded-lg text-[10px] font-black uppercase tracking-widest transition-all cursor-pointer',
                            $activeClass . ' shadow-sm scale-105 z-10' => session('market_type', 'crypto') === $marketValue,
                            'text-base-content/50 hover:text-base-content' => session('market_type', 'crypto') !== $marketValue
                        ])>
                            {{ $marketValue === 'all' ? 'All' : strtoupper($marketValue) }}
                        </button>
                    </form>
                    @endforeach
                </div>
            </div>

            <!-- Currency Switcher -->
            <div class="flex items-center justify-between">
                <span class="text-xs font-bold px-2">Currency</span>
                <div class="flex bg-base-200 p-1 rounded-xl shadow-inner border border-base-300">
                    @foreach(['USD' => 'bg-info text-info-content', 'PHP' => 'bg-success text-success-content'] as $currencyValue => $activeClass)
                    <form action="{{ route('trading-mode.update') }}" method="POST" class="m-0">
                        @csrf
                        <input type="hidden" name="preferred_currency" value="{{ $currencyValue }}">
                        <button type="submit" @class([
                            'px-2 py-1 rounded-lg text-[10px] font-black uppercase tracking-widest transition-all cursor-pointer',
                            $activeClass . ' shadow-sm scale-105 z-10' => session('preferred_currency', 'USD') === $currencyValue,
                            'text-base-content/50 hover:text-base-content' => session('preferred_currency', 'USD') !== $currencyValue
                        ])>
                            {{ $currencyValue }}
                        </button>
                    </form>
                    @endforeach
                </div>
            </div>
        </div>

        <!-- User Profile & Theme -->
        <div class="p-4 border-t border-base-200 flex items-center justify-between bg-base-100 group-[.sidebar-collapsed]:justify-center">
            <div class="flex items-center gap-3 overflow-hidden group-[.sidebar-collapsed]:hidden">
                <div class="w-10 h-10 rounded-full bg-primary text-primary-content flex justify-center items-center shrink-0 shadow-sm">
                    @if ($profilePicture ?? false)
                        <img src="{{ $profilePicture }}" alt="Profile" class="w-full h-full object-cover rounded-full">
                    @else
                        <span class="font-bold text-sm">{{ $initials ?? substr(Auth::user()->name, 0, 2) }}</span>
                    @endif
                </div>
                <div class="flex-1 min-w-0 transition-opacity duration-300">
                    <p class="text-sm font-bold truncate">{{ Auth::user()->name }}</p>
                    <p class="text-[10px] opacity-60 truncate">{{ Auth::user()->email }}</p>
                </div>
            </div>
            
            <div class="dropdown dropdown-top dropdown-end group-[.sidebar-collapsed]:dropdown-right group-[.sidebar-collapsed]:dropdown-bottom">
                <div tabindex="0" role="button" class="btn btn-ghost btn-circle btn-sm group-[.sidebar-collapsed]:w-10 group-[.sidebar-collapsed]:h-10">
                    <div class="hidden group-[.sidebar-collapsed]:flex w-10 h-10 rounded-full bg-primary text-primary-content justify-center items-center shrink-0 shadow-sm">
                        @if ($profilePicture ?? false)
                            <img src="{{ $profilePicture }}" alt="Profile" class="w-full h-full object-cover rounded-full">
                        @else
                            <span class="font-bold text-sm">{{ $initials ?? substr(Auth::user()->name, 0, 2) }}</span>
                        @endif
                    </div>
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 group-[.sidebar-collapsed]:hidden" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 5v.01M12 12v.01M12 19v.01M12 6a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2z" /></svg>
                </div>
                <ul tabindex="0" class="dropdown-content z-[1] menu p-2 shadow-xl bg-base-100 rounded-box w-52 border border-base-200 mb-2 group-[.sidebar-collapsed]:ml-2">
                    <li>
                        <a id="theme-toggle" class="flex justify-between cursor-pointer">
                            Toggle Theme
                            <svg id="sun-icon" class="hidden w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364-6.364l-.707.707M6.343 17.657l-.707.707m12.728 0l-.707-.707M6.343 6.343l-.707-.707M12 5a7 7 0 100 14 7 7 0 000-14z"></path></svg>
                            <svg id="moon-icon" class="hidden w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z"></path></svg>
                        </a>
                    </li>
                    <li><a href="/profile">Profile Settings</a></li>
                    @if(Auth::user()->is_admin)
                        <li><a href="{{ route('admin.dashboard') }}" class="text-primary font-bold">Admin Panel</a></li>
                    @endif
                    <div class="divider my-1"></div>
                    <li>
                        <button
                            type="button"
                            class="text-error hover:bg-error/10 hover:text-error focus:outline-none w-full text-left"
                            onclick="document.getElementById('logout-form').submit();">
                            Sign Out
                        </button>
                    </li>
                </ul>
            </div>
            <form id="logout-form" action="{{ url('/logout') }}" method="POST" class="hidden" data-turbo="false">
                @csrf
            </form>
        </div>
    @else
        <!-- Guest View -->
        <div class="flex-1 flex flex-col p-6 gap-4">
            <a href="/#features" class="hover:text-primary transition-colors font-medium">Features</a>
            <a href="/#how-it-works" class="hover:text-primary transition-colors font-medium">How it Works</a>
            <a href="/#why-tradexy" class="hover:text-primary transition-colors font-medium">Why Tradexy</a>
            
            <div class="mt-auto flex flex-col gap-3">
                <a href="{{ route('login') }}" class="btn btn-ghost">Log in</a>
                <a href="{{ route('register') }}" class="btn btn-primary">Get Started</a>
            </div>
        </div>
    @endauth
</aside>

<script>
    (function () {
        // These helpers must be re-wired on every Turbo navigation because the
        // sidebar lives inside the drawer-side which is part of the replaced body.
        const updateThemeIcons = () => {
            const currentTheme = document.documentElement.getAttribute('data-theme');
            const sunIcon = document.getElementById('sun-icon');
            const moonIcon = document.getElementById('moon-icon');

            if (currentTheme === 'dark') {
                if (sunIcon) sunIcon.classList.remove('hidden');
                if (moonIcon) moonIcon.classList.add('hidden');
            } else {
                if (sunIcon) sunIcon.classList.add('hidden');
                if (moonIcon) moonIcon.classList.remove('hidden');
            }
        };

        const toggleTheme = () => {
            const currentTheme = document.documentElement.getAttribute('data-theme');
            const newTheme = currentTheme === 'dark' ? 'light' : 'dark';
            document.documentElement.setAttribute('data-theme', newTheme);
            localStorage.setItem('theme', newTheme);
            updateThemeIcons();
        };

        window.toggleDesktopSidebar = function () {
            const drawer = document.getElementById('app-drawer');
            if (!drawer) return;

            if (drawer.classList.contains('sidebar-collapsed')) {
                drawer.classList.remove('sidebar-collapsed');
                localStorage.setItem('sidebar-collapsed', 'false');
            } else {
                drawer.classList.add('sidebar-collapsed');
                localStorage.setItem('sidebar-collapsed', 'true');
            }
            window.updateSidebarIcon();
        };

        window.updateSidebarIcon = function () {
            const drawer = document.getElementById('app-drawer');
            const icon = document.getElementById('collapse-icon');
            if (!icon) return;

            if (drawer && drawer.classList.contains('sidebar-collapsed')) {
                icon.innerHTML = '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 5l7 7-7 7M5 5l7 7-7 7" />';
            } else {
                icon.innerHTML = '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 19l-7-7 7-7m8 14l-7-7 7-7" />';
            }
        };

        const init = () => {
            const themeToggleBtn = document.getElementById('theme-toggle');
            if (themeToggleBtn && !themeToggleBtn.dataset.bound) {
                themeToggleBtn.addEventListener('click', toggleTheme);
                themeToggleBtn.dataset.bound = '1';
            }
            updateThemeIcons();
            window.updateSidebarIcon();
        };

        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', init, { once: true });
        } else {
            init();
        }
    })();
</script>