<header class="w-full text-sm not-has-[nav]:hidden border-b border-transparent dark:border-transparent py-4 relative">
    <div class="max-w-7xl mx-auto px-6">
        <nav class=" flex items-center justify-between gap-4 w-full">
            <div class="flex items-center gap-6">
                <a href="{{ url('/') }}" class="font-bold text-lg flex items-center gap-2">
                    {{-- <x-application-logo class="w-8 h-8 fill-current text-black dark:text-white" /> --}}
                    <img src="{{ asset('images/logo.png') }}" alt="Trading Journal Logo" class="h-8">
                    {{ config('app.name', 'Laravel') }}
                </a>

                <div class="hidden md:flex items-center gap-6 text-gray-600 dark:text-gray-400">
                    @auth
                        <a href="/dashboard"
                            class="hover:text-gray-900 dark:hover:text-white transition-colors">Dashboard</a>
                        <a href="/trades" class="hover:text-gray-900 dark:hover:text-white transition-colors">Trades</a>
                        <a href="/trades/gallery"
                            class="hover:text-gray-900 dark:hover:text-white transition-colors">Win/Loss Gallery</a>
                        <a href="/balances" class="hover:text-gray-900 dark:hover:text-white transition-colors">Balances</a>
                        <a href="/strategies"
                            class="hover:text-gray-900 dark:hover:text-white transition-colors">Strategies</a>
                        <a href="/pnl-calendar" class="hover:text-gray-900 dark:hover:text-white transition-colors">PnL
                            Calendar</a>
                    @else
                        <a href="#features" class="hover:text-gray-900 dark:hover:text-white transition-colors">Features</a>
                        <a href="#how-it-works" class="hover:text-gray-900 dark:hover:text-white transition-colors">How it
                            Works</a>
                        <a href="#why-tradexy" class="hover:text-gray-900 dark:hover:text-white transition-colors">Why
                            Tradexy</a>
                    @endauth
                </div>
            </div>

            @auth
                <!-- Desktop Avatar -->
                <div class="relative hidden md:block z-[60]">
                    <div class="w-10 h-10 rounded-full bg-blue-500 text-white flex justify-center items-center cursor-pointer shadow hover:bg-blue-600 transition-colors"
                        id="avatar-btn">
                        @if ($profilePicture)
                            <img src="{{ $profilePicture }}" alt="{{ Auth::user()->name ?? 'User' }}"
                                class="rounded-full w-full h-full object-cover">
                        @else
                            @if (!empty($initials))
                                <span class="font-medium text-sm">{{ $initials }}</span>
                            @endif
                        @endif
                    </div>

                    <!-- Desktop Dropdown -->
                    <div id="avatar-menu"
                        class="hidden absolute top-12 right-0 mt-2 w-56 bg-base-100 rounded-lg shadow-xl border border-base-200 py-1 overflow-hidden z-[60]">
                        <div class="px-4 py-3 border-b border-base-200 bg-base-200/50">
                            <p class="text-sm font-medium text-base-content truncate">{{ Auth::user()->name ?? 'User' }}</p>
                            <p class="text-xs text-base-content/70 truncate">{{ Auth::user()->email ?? '' }}</p>
                        </div>
                        <ul class="py-1">
                            <li><a href="/profile"
                                    class="block px-4 py-2 text-sm text-base-content hover:bg-base-200 transition-colors">Profile</a>
                            </li>
                            <li><a href="/settings"
                                    class="block px-4 py-2 text-sm text-base-content hover:bg-base-200 transition-colors">Settings</a>
                            </li>
                            <li class="border-t border-base-200 mt-1 pt-1">
                                <form action="/logout" method="post" class="w-full m-0 p-0">
                                    @csrf
                                    <button type="submit"
                                        class="w-full text-left px-4 py-2 text-sm text-error hover:bg-error/10 transition-colors cursor-pointer">
                                        Logout
                                    </button>
                                </form>
                            </li>
                        </ul>
                    </div>
                </div>

                <!-- Hamburger Mobile -->
                <div id="menu-btn" class="md:hidden cursor-pointer">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-6 h-6" viewBox="0 0 640 640"
                        fill="currentColor"><!--!Font Awesome Free v7.2.0 by @fontawesome - https://fontawesome.com License - https://fontawesome.com/license/free Copyright 2026 Fonticons, Inc.-->
                        <path
                            d="M96 160C96 142.3 110.3 128 128 128L512 128C529.7 128 544 142.3 544 160C544 177.7 529.7 192 512 192L128 192C110.3 192 96 177.7 96 160zM96 320C96 302.3 110.3 288 128 288L512 288C529.7 288 544 302.3 544 320C544 337.7 529.7 352 512 352L128 352C110.3 352 96 337.7 96 320zM544 480C544 497.7 529.7 512 512 512L128 512C110.3 512 96 497.7 96 480C96 462.3 110.3 448 128 448L512 448C529.7 448 544 462.3 544 480z" />
                    </svg>
                </div>
                <!-- Mobile Dropdown -->
                <div id="dropdown-menu" class="hidden md:hidden fixed top-16 left-0 right-0 bottom-0 bg-gray-200 px-4">
                    @auth
                        <ul class="">
                            <li class="py-4">
                                <a href="/dashboard"
                                    class="flex items-center gap-2 hover:text-gray-900 dark:hover:text-white transition-colors">
                                    {{-- Dashboard Icon (Heroicons: squares-2x2) --}}
                                    <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24"
                                        stroke="currentColor" stroke-width="1.5">
                                        <path stroke-linecap="round" stroke-linejoin="round"
                                            d="M3.75 6A2.25 2.25 0 0 1 6 3.75h2.25A2.25 2.25 0 0 1 10.5 6v2.25a2.25 2.25 0 0 1-2.25 2.25H6a2.25 2.25 0 0 1-2.25-2.25V6ZM3.75 15.75A2.25 2.25 0 0 1 6 13.5h2.25a2.25 2.25 0 0 1 2.25 2.25V18a2.25 2.25 0 0 1-2.25 2.25H6A2.25 2.25 0 0 1 3.75 18v-2.25ZM13.5 6a2.25 2.25 0 0 1 2.25-2.25H18A2.25 2.25 0 0 1 20.25 6v2.25A2.25 2.25 0 0 1 18 10.5h-2.25a2.25 2.25 0 0 1-2.25-2.25V6ZM13.5 15.75a2.25 2.25 0 0 1 2.25-2.25H18a2.25 2.25 0 0 1 2.25 2.25V18A2.25 2.25 0 0 1 18 20.25h-2.25A2.25 2.25 0 0 1 13.5 18v-2.25Z" />
                                    </svg>
                                    Dashboard
                                </a>
                            </li>
                            <li class="py-4">
                                <a href="/trades"
                                    class="flex items-center gap-2 hover:text-gray-900 dark:hover:text-white transition-colors">
                                    {{-- Trades Icon (Heroicons: chart-bar) --}}
                                    <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24"
                                        stroke="currentColor" stroke-width="1.5">
                                        <path stroke-linecap="round" stroke-linejoin="round"
                                            d="M3 13.125C3 12.504 3.504 12 4.125 12h2.25c.621 0 1.125.504 1.125 1.125v6.75C7.5 20.496 6.996 21 6.375 21h-2.25A1.125 1.125 0 0 1 3 19.875v-6.75ZM9.75 8.625c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125v11.25c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 0 1-1.125-1.125V8.625ZM16.5 4.125c0-.621.504-1.125 1.125-1.125h2.25C20.496 3 21 3.504 21 4.125v15.75c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 0 1-1.125-1.125V4.125Z" />
                                    </svg>
                                    Trades
                                </a>
                            </li>
                            <li class="py-4">
                                <a href="/trades/gallery"
                                    class="flex items-center gap-2 hover:text-gray-900 dark:hover:text-white transition-colors">
                                    {{-- Gallery Icon (Heroicons: photo) --}}
                                    <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24"
                                        stroke="currentColor" stroke-width="1.5">
                                        <path stroke-linecap="round" stroke-linejoin="round"
                                            d="m2.25 15.75 5.159-5.159a2.25 2.25 0 0 1 3.182 0l5.159 5.159m-1.5-1.5 1.409-1.409a2.25 2.25 0 0 1 3.182 0l2.909 2.909m-18 3.75h16.5a1.5 1.5 0 0 0 1.5-1.5V6a1.5 1.5 0 0 0-1.5-1.5H3.75A1.5 1.5 0 0 0 2.25 6v12a1.5 1.5 0 0 0 1.5 1.5Zm10.5-11.25h.008v.008h-.008V8.25Zm.375 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Z" />
                                    </svg>
                                    Win/Loss Gallery
                                </a>
                            </li>
                            <li class="py-4">
                                <a href="/balances"
                                    class="flex items-center gap-2 hover:text-gray-900 dark:hover:text-white transition-colors">
                                    {{-- Balance Icon (Heroicons: wallet) --}}
                                    <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24"
                                        stroke="currentColor" stroke-width="1.5">
                                        <path stroke-linecap="round" stroke-linejoin="round"
                                            d="M21 12a2.25 2.25 0 0 0-2.25-2.25H15a3 3 0 1 1-6 0H5.25A2.25 2.25 0 0 0 3 12m18 0v6a2.25 2.25 0 0 1-2.25 2.25H5.25A2.25 2.25 0 0 1 3 18v-6m18 0V9M3 12V9m18 0a2.25 2.25 0 0 0-2.25-2.25H5.25A2.25 2.25 0 0 0 3 9m18 0V6a2.25 2.25 0 0 0-2.25-2.25H5.25A2.25 2.25 0 0 0 3 6v3" />
                                    </svg>
                                    Balances
                                </a>
                            </li>
                            <li class="py-4">
                                <a href="/strategies"
                                    class="flex items-center gap-2 hover:text-gray-900 dark:hover:text-white transition-colors">
                                    {{-- Strategies Icon (Heroicons: puzzle-piece) --}}
                                    <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24"
                                        stroke="currentColor" stroke-width="1.5">
                                        <path stroke-linecap="round" stroke-linejoin="round"
                                            d="M14.25 6.087c0-.355.186-.676.401-.959.221-.29.349-.634.349-1.003 0-1.036-1.007-1.875-2.25-1.875s-2.25.84-2.25 1.875c0 .369.128.713.349 1.003.215.283.401.604.401.959v0a.64.64 0 0 1-.657.643 48.39 48.39 0 0 1-4.163-.3c.186 1.613.293 3.25.315 4.907a.656.656 0 0 1-.658.663v0c-.355 0-.676-.186-.959-.401a1.647 1.647 0 0 0-1.003-.349c-1.036 0-1.875 1.007-1.875 2.25s.84 2.25 1.875 2.25c.369 0 .713-.128 1.003-.349.283-.215.604-.401.959-.401v0c.31 0 .555.26.532.57a48.039 48.039 0 0 1-.642 5.056c1.518.19 3.058.309 4.616.354a.64.64 0 0 0 .657-.643v0c0-.355-.186-.676-.401-.959a1.647 1.647 0 0 1-.349-1.003c0-1.035 1.008-1.875 2.25-1.875 1.243 0 2.25.84 2.25 1.875 0 .369-.128.713-.349 1.003-.215.283-.4.604-.4.959v0c0 .333.277.599.61.58a48.1 48.1 0 0 0 5.427-.63 48.05 48.05 0 0 0 .582-4.717.532.532 0 0 0-.533-.57v0c-.355 0-.676.186-.959.401-.29.221-.634.349-1.003.349-1.035 0-1.875-1.007-1.875-2.25s.84-2.25 1.875-2.25c.37 0 .713.128 1.003.349.283.215.604.401.959.401v0a.656.656 0 0 0 .658-.663 48.422 48.422 0 0 0-.37-5.36c-1.886.342-3.81.574-5.766.689a.578.578 0 0 1-.61-.58v0Z" />
                                    </svg>
                                    Strategies
                                </a>
                            </li>
                            <li class="py-4">
                                <a href="/pnl-calendar"
                                    class="flex items-center gap-2 hover:text-gray-900 dark:hover:text-white transition-colors">
                                    {{-- PnL Calendar Icon (Heroicons: calendar) --}}
                                    <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24"
                                        stroke="currentColor" stroke-width="1.5">
                                        <path stroke-linecap="round" stroke-linejoin="round"
                                            d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 012.25-2.25h13.5A2.25 2.25 0 0121 7.5v11.25m-18 0A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75m-18 0v-7.5A2.25 2.25 0 015.25 9h13.5A2.25 2.25 0 0121 11.25v7.5" />
                                    </svg>
                                    PnL Calendar
                                </a>
                            </li>
                            <li class="py-4">
                                <form action="/logout" method="post">
                                    @csrf
                                    <button type="submit"
                                        class="text-red-600 dark:text-red-400 hover:text-red-900 dark:hover:text-white transition-colors cursor-pointer">Logout</button>
                                </form>
                            </li>
                        </ul>
                    @else
                        <ul class="">
                            <li class="py-4">
                                <a href="#features"
                                    class="flex items-center gap-2 hover:text-gray-900 dark:hover:text-white transition-colors">
                                    {{-- Features Icon (Heroicons: star) --}}
                                    <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24"
                                        stroke="currentColor" stroke-width="1.5">
                                        <path stroke-linecap="round" stroke-linejoin="round"
                                            d="M11.48 3.499a.562.562 0 0 1 1.04 0l2.125 5.111a.563.563 0 0 0 .475.345l5.518.442c.499.04.701.663.321.988l-4.204 3.602a.563.563 0 0 0-.182.557l1.285 5.385a.562.562 0 0 1-.84.61l-4.725-2.885a.562.562 0 0 0-.586 0L6.982 20.54a.562.562 0 0 1-.84-.61l1.285-5.386a.562.562 0 0 0-.182-.557l-4.204-3.602a.562.562 0 0 1 .321-.988l5.518-.442a.563.563 0 0 0 .475-.345L11.48 3.5Z" />
                                    </svg>
                                    Features
                                </a>
                            </li>
                            <li class="py-4">
                                <a href="#how-it-works"
                                    class="flex items-center gap-2 hover:text-gray-900 dark:hover:text-white transition-colors">
                                    {{-- How it Works Icon (Heroicons: light-bulb) --}}
                                    <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24"
                                        stroke="currentColor" stroke-width="1.5">
                                        <path stroke-linecap="round" stroke-linejoin="round"
                                            d="M12 18v-5.25m0 0a6.01 6.01 0 0 0 1.5-.189m-1.5.189a6.01 6.01 0 0 1-1.5-.189m3.75 7.478a12.06 12.06 0 0 1-4.5 0m3.75 2.383a14.406 14.406 0 0 1-3 0M14.25 18v-.192c0-.983.658-1.823 1.508-2.316a7.5 7.5 0 1 0-7.517 0c.85.493 1.509 1.333 1.509 2.316V18" />
                                    </svg>
                                    How it Works
                                </a>
                            </li>
                            <li class="py-4">
                                <a href="#why-tradexy"
                                    class="flex items-center gap-2 hover:text-gray-900 dark:hover:text-white transition-colors">
                                    {{-- Why Tradexy Icon (Heroicons: heart) --}}
                                    <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24"
                                        stroke="currentColor" stroke-width="1.5">
                                        <path stroke-linecap="round" stroke-linejoin="round"
                                            d="M21 8.25c0-2.485-2.099-4.5-4.688-4.5-1.935 0-3.597 1.126-4.312 2.733-.715-1.607-2.377-2.733-4.313-2.733C5.1 3.75 3 5.765 3 8.25c0 7.22 9 12 9 12s9-4.78 9-12Z" />
                                    </svg>
                                    Why Tradexy
                                </a>
                            </li>
                        </ul>
                    @endauth
                </div>

            @else
                <div class="flex items-center gap-4">
                    @if (Route::has('login'))
                        <a href="{{ route('login') }}"
                            class="text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white transition-colors">
                            Log in
                        </a>
                    @endif

                    @if (Route::has('register'))
                        <a href="{{ route('register') }}"
                            class="inline-block px-4 py-2 bg-gray-900 dark:bg-white text-white dark:text-gray-900 rounded-lg text-sm font-medium hover:bg-gray-800 dark:hover:bg-gray-200 transition-colors">
                            Register
                        </a>
                    @endif
                </div>
            @endauth
        </nav>
    </div>
</header>
<script>
    let menuBtn = document.getElementById('menu-btn')
    let avatarBtn = document.getElementById('avatar-btn')
    let dropdownMenu = document.getElementById('dropdown-menu')
    let avatarMenu = document.getElementById('avatar-menu')
    if (menuBtn && dropdownMenu) {
        menuBtn.addEventListener('click', function () {
            dropdownMenu.classList.toggle('hidden')
        })
    }

    if (avatarBtn && avatarMenu) {
        avatarBtn.addEventListener('click', function (event) {
            event.stopPropagation()
            avatarMenu.classList.toggle('hidden')
        })
    }

    document.addEventListener('click', function (event) {
        if (avatarBtn && avatarMenu) {
            if (!avatarBtn.contains(event.target) && !avatarMenu.contains(event.target)) {
                avatarMenu.classList.add('hidden')
            }
        }
    })
</script>