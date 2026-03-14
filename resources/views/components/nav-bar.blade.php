<header class="w-full text-sm border-b border-base-200 dark:border-base-800 py-4 relative bg-base-100">
    <div class="max-w-7xl mx-auto px-6">
        <nav class="flex items-center justify-between gap-4 w-full">
            <!-- Logo Section -->
            <div class="flex items-center gap-8">
                <a href="{{ url('/') }}" class="font-bold text-lg flex items-center gap-2">
                    <img src="{{ asset('images/logo.png') }}" alt="Logo" class="h-8">
                    {{ config('app.name', 'Tradexy') }}
                </a>

                <!-- Desktop Navigation Links -->
                @auth
                    <div class="hidden lg:flex items-center gap-6 text-base-content/70">
                        <a href="/dashboard" class="hover:text-base-content transition-colors font-medium">Dashboard</a>
                        <a href="/trades" class="hover:text-base-content transition-colors font-medium">Trades</a>
                        <a href="/trades/gallery" class="hover:text-base-content transition-colors font-medium">Gallery</a>
                        <a href="/balances" class="hover:text-base-content transition-colors font-medium">Balances</a>
                        <a href="/strategies" class="hover:text-base-content transition-colors font-medium">Strategies</a>
                        <a href="/pnl-calendar" class="hover:text-base-content transition-colors font-medium">PnL Calendar</a>
                    </div>
                @else
                    <div class="hidden lg:flex items-center gap-6 text-base-content/70">
                        <a href="#features" class="hover:text-base-content transition-colors underline-offset-4 hover:underline">Features</a>
                        <a href="#how-it-works" class="hover:text-base-content transition-colors underline-offset-4 hover:underline">How it Works</a>
                        <a href="#why-tradexy" class="hover:text-base-content transition-colors underline-offset-4 hover:underline">Why Tradexy</a>
                    </div>
                @endauth
            </div>

            <!-- Desktop Actions Section -->
            <div class="hidden lg:flex items-center gap-4">
                @auth
                    <!-- Global Account Switcher -->
                    <div class="flex items-center bg-base-200 p-1 rounded-xl shadow-inner border border-base-300">
                        @foreach(['real' => 'bg-primary text-primary-content', 'demo' => 'bg-warning text-warning-content', 'all' => 'bg-base-300 text-base-content'] as $modeValue => $activeClass)
                        <form action="{{ route('trading-mode.update') }}" method="POST" class="m-0">
                            @csrf
                            <input type="hidden" name="account_mode" value="{{ $modeValue }}">
                            <button type="submit" @class([
                                'px-2 py-1.5 rounded-lg text-[10px] font-black uppercase tracking-widest transition-all cursor-pointer',
                                $activeClass . ' shadow-sm scale-110 z-10' => session('account_mode', 'real') === $modeValue,
                                'text-base-content/40 hover:text-base-content' => session('account_mode', 'real') !== $modeValue
                            ])>
                                {{ $modeValue === 'all' ? 'All' : ucfirst($modeValue) }}
                            </button>
                        </form>
                        @endforeach
                    </div>

                    <!-- Market Switcher -->
                    <div class="flex items-center bg-base-200 p-1 rounded-xl shadow-inner border border-base-300">
                        @foreach(['crypto' => 'bg-secondary text-secondary-content', 'pse' => 'bg-accent text-accent-content', 'all' => 'bg-base-300 text-base-content'] as $marketValue => $activeClass)
                        <form action="{{ route('trading-mode.update') }}" method="POST" class="m-0">
                            @csrf
                            <input type="hidden" name="market_type" value="{{ $marketValue }}">
                            <button type="submit" @class([
                                'px-2 py-1.5 rounded-lg text-[10px] font-black uppercase tracking-widest transition-all cursor-pointer',
                                $activeClass . ' shadow-sm scale-110 z-10' => session('market_type', 'crypto') === $marketValue,
                                'text-base-content/40 hover:text-base-content' => session('market_type', 'crypto') !== $marketValue
                            ])>
                                {{ $marketValue === 'all' ? 'All' : strtoupper($marketValue) }}
                            </button>
                        </form>
                        @endforeach
                    </div>

                    <!-- Avatar/Profile Dropdown -->
                    <div class="relative ml-2">
                        <button id="avatar-btn" class="w-10 h-10 rounded-full bg-primary text-primary-content flex justify-center items-center cursor-pointer shadow hover:scale-105 transition-transform overflow-hidden">
                            @if ($profilePicture)
                                <img src="{{ $profilePicture }}" alt="Profile" class="w-full h-full object-cover">
                            @else
                                <span class="font-bold text-sm">{{ $initials }}</span>
                            @endif
                        </button>
                        
                        <div id="avatar-menu" class="hidden absolute top-full right-0 mt-3 w-56 bg-base-100 rounded-2xl shadow-2xl border border-base-200 py-2 overflow-hidden z-[100]">
                            <div class="px-4 py-3 border-b border-base-200 bg-base-200/30">
                                <p class="text-sm font-bold truncate text-base-content">{{ Auth::user()->name }}</p>
                                <p class="text-xs truncate text-base-content/60">{{ Auth::user()->email }}</p>
                            </div>
                            <ul class="py-2">
                                <li><a href="/profile" class="flex items-center px-4 py-2.5 text-sm hover:bg-base-200 transition-colors">Your Profile</a></li>
                                <li class="border-t border-base-200 mt-2 pt-2">
                                    <form action="/logout" method="post" class="m-0">
                                        @csrf
                                        <button type="submit" class="w-full text-left px-4 py-2.5 text-sm text-error font-medium hover:bg-error/5 transition-colors cursor-pointer">
                                            Sign out
                                        </button>
                                    </form>
                                </li>
                            </ul>
                        </div>
                    </div>
                @else
                    <div class="flex items-center gap-4">
                        <a href="{{ route('login') }}" class="btn btn-ghost btn-sm">Log in</a>
                        <a href="{{ route('register') }}" class="btn btn-primary btn-sm px-6">Get Started</a>
                    </div>
                @endauth
            </div>

            <!-- Mobile Hamburger Section -->
            <div class="lg:hidden flex items-center gap-4">
                <button id="menu-btn" class="btn btn-ghost btn-circle">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                    </svg>
                </button>
            </div>
        </nav>

        <!-- Mobile Full-screen Dropdown -->
        <div id="dropdown-menu" class="hidden lg:hidden fixed inset-0 top-16 bg-base-100 z-[99] flex flex-col px-6">
            @auth
                <!-- User Profile Header -->
                <div class="py-8 border-b border-base-200">
                    <div class="flex items-center gap-5">
                        <div class="w-16 h-16 rounded-full bg-primary text-primary-content flex justify-center items-center shadow-lg overflow-hidden">
                            @if ($profilePicture)
                                <img src="{{ $profilePicture }}" alt="Profile" class="w-full h-full object-cover">
                            @else
                                <span class="font-black text-xl">{{ $initials }}</span>
                            @endif
                        </div>
                        <div>
                            <p class="font-black text-xl text-base-content uppercase tracking-tight">{{ Auth::user()->name }}</p>
                            <p class="text-sm text-base-content/60">{{ Auth::user()->email }}</p>
                        </div>
                    </div>
                    <div class="mt-6 grid grid-cols-2 gap-3">
                        <a href="/profile" class="btn btn-outline btn-md">Profile</a>
                        <form action="/logout" method="post" class="m-0">
                            @csrf
                            <button type="submit" class="btn btn-error btn-outline btn-md w-full">Sign Out</button>
                        </form>
                    </div>
                </div>

                <!-- Navigation List -->
                <div class="flex-1 overflow-y-auto py-6 space-y-2">
                    <p class="text-[10px] font-black uppercase text-base-content/40 tracking-widest mb-2 px-2">Navigation</p>
                    <a href="/dashboard" class="flex items-center gap-4 p-4 rounded-xl bg-base-200/50 hover:bg-base-200 transition-colors font-bold text-lg">Dashboard</a>
                    <a href="/trades" class="flex items-center gap-4 p-4 rounded-xl bg-base-200/50 hover:bg-base-200 transition-colors font-bold text-lg">Trade Log</a>
                    <a href="/trades/gallery" class="flex items-center gap-4 p-4 rounded-xl bg-base-200/50 hover:bg-base-200 transition-colors font-bold text-lg">Visual Gallery</a>
                    <a href="/balances" class="flex items-center gap-4 p-4 rounded-xl bg-base-200/50 hover:bg-base-200 transition-colors font-bold text-lg">Account Balances</a>
                    <a href="/strategies" class="flex items-center gap-4 p-4 rounded-xl bg-base-200/50 hover:bg-base-200 transition-colors font-bold text-lg">My Strategies</a>
                    <a href="/pnl-calendar" class="flex items-center gap-4 p-4 rounded-xl bg-base-200/50 hover:bg-base-200 transition-colors font-bold text-lg text-primary">PnL Calendar</a>
                </div>

                <!-- Global Modes (Mobile) -->
                <div class="py-6 border-t border-base-200 bg-base-200/30 -mx-6 px-6">
                    <p class="text-[10px] font-black uppercase text-base-content/40 tracking-widest mb-4">View Preferences</p>
                    <div class="space-y-4">
                        <div class="flex items-center justify-between bg-base-100 p-2 rounded-2xl border border-base-200">
                            <span class="text-xs font-black uppercase tracking-tight pl-2">Account Mode</span>
                            <div class="flex bg-base-200 p-1 rounded-xl">
                                @foreach(['real', 'demo', 'all'] as $mv)
                                    <form action="{{ route('trading-mode.update') }}" method="POST" class="m-0">
                                        @csrf
                                        <input type="hidden" name="account_mode" value="{{ $mv }}">
                                        <button type="submit" @class([
                                            'px-4 py-1.5 rounded-lg text-[10px] font-black uppercase',
                                            'bg-primary text-primary-content shadow' => session('account_mode', 'real') === $mv,
                                            'text-base-content/40' => session('account_mode', 'real') !== $mv
                                        ])>{{ strtoupper($mv) }}</button>
                                    </form>
                                @endforeach
                            </div>
                        </div>
                        <div class="flex items-center justify-between bg-base-100 p-2 rounded-2xl border border-base-200">
                            <span class="text-xs font-black uppercase tracking-tight pl-2">Market Type</span>
                            <div class="flex bg-base-200 p-1 rounded-xl">
                                @foreach(['crypto', 'pse', 'all'] as $mv)
                                    <form action="{{ route('trading-mode.update') }}" method="POST" class="m-0">
                                        @csrf
                                        <input type="hidden" name="market_type" value="{{ $mv }}">
                                        <button type="submit" @class([
                                            'px-4 py-1.5 rounded-lg text-[10px] font-black uppercase',
                                            'bg-secondary text-secondary-content shadow' => session('market_type', 'crypto') === $mv,
                                            'text-base-content/40' => session('market_type', 'crypto') !== $mv
                                        ])>{{ strtoupper($mv) }}</button>
                                    </form>
                                @endforeach
                            </div>
                        </div>
                    </div>
                </div>
            @else
                <div class="flex flex-col gap-4 py-10">
                    <a href="{{ route('login') }}" class="btn btn-block btn-lg btn-ghost">Log In</a>
                    <a href="{{ route('register') }}" class="btn btn-block btn-lg btn-primary">Start Journaling</a>
                </div>
            @endauth
        </div>
    </div>
</header>

<script>
    document.addEventListener('DOMContentLoaded', () => {
        const menuBtn = document.getElementById('menu-btn');
        const dropdownMenu = document.getElementById('dropdown-menu');
        const avatarBtn = document.getElementById('avatar-btn');
        const avatarMenu = document.getElementById('avatar-menu');

        // Mobile menu toggle
        if (menuBtn && dropdownMenu) {
            menuBtn.addEventListener('click', () => {
                dropdownMenu.classList.toggle('hidden');
                document.body.classList.toggle('overflow-hidden'); // Prevent background scroll
            });
        }

        // Desktop avatar toggle
        if (avatarBtn && avatarMenu) {
            avatarBtn.addEventListener('click', (e) => {
                e.stopPropagation();
                avatarMenu.classList.toggle('hidden');
            });

            document.addEventListener('click', (e) => {
                if (!avatarBtn.contains(e.target) && !avatarMenu.contains(e.target)) {
                    avatarMenu.classList.add('hidden');
                }
            });
        }
    });
</script>