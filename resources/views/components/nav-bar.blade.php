<header class="w-full text-sm not-has-[nav]:hidden border-b border-transparent dark:border-transparent py-4">
    <div class="max-w-7xl mx-auto px-6">
        <nav class="flex items-center justify-between gap-4 w-full">
            <div class="flex items-center gap-6">
                <a href="{{ url('/') }}" class="font-bold text-lg flex items-center gap-2">
                    {{-- <x-application-logo class="w-8 h-8 fill-current text-black dark:text-white" /> --}}
                    <img src="{{ asset('images/logo.png') }}" alt="Trading Journal Logo" class="h-8">
                    {{ config('app.name', 'Laravel') }}
                </a>
                
                <div class="hidden md:flex items-center gap-6 text-gray-600 dark:text-gray-400">
                    @auth
                        <a href="" class="hover:text-gray-900 dark:hover:text-white transition-colors">Dashboard</a>
                        <a href="" class="hover:text-gray-900 dark:hover:text-white transition-colors">Trade Logs</a>
                        <a href="" class="hover:text-gray-900 dark:hover:text-white transition-colors">Balance</a>
                    @else
                        <a href="" class="hover:text-gray-900 dark:hover:text-white transition-colors">Features</a>
                        <a href="" class="hover:text-gray-900 dark:hover:text-white transition-colors">How it Works</a>
                        <a href="" class="hover:text-gray-900 dark:hover:text-white transition-colors">Why Tradexy</a>
                    @endauth
                </div>
            </div>

            @auth
            <form action="/logout" method="post">
                @csrf
                <button type="submit" class="text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white transition-colors cursor-pointer">Logout</button>
            </form>
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