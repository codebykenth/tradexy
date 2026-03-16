<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" prefix="og: https://ogp.me/ns#">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <!-- Reverb Dynamic Config for frontend without needing Vite rebuilds -->
    <meta name="reverb-app-key" content="{{ config('broadcasting.connections.reverb.key') }}">
    <meta name="reverb-host" content="{{ config('broadcasting.connections.reverb.options.host') }}">
    <meta name="reverb-port" content="{{ config('broadcasting.connections.reverb.options.port') }}">
    <meta name="reverb-scheme" content="{{ config('broadcasting.connections.reverb.options.scheme') }}">

    @php
        $pageTitle = $title ?? config('app.name');
        $pageDescription = $description ?? 'Tradexy — A modern trading journal designed for Crypto and PSE (PH Market) traders to track setups, analyze performance, and refine their edge.';
        $pageImage = $image ?? asset('images/logo.png');
        $pageUrl = url()->current();
    @endphp

    <title>{{ $pageTitle }}</title>
    
    <!-- Primary Meta Tags -->
    <meta name="title" content="{{ $pageTitle }}">
    <meta name="description" content="{{ $pageDescription }}">
    <meta name="theme-color" content="#3b82f6">
    
    <!-- Open Graph / Facebook / Discord -->
    <meta property="og:type" content="website">
    <meta property="og:url" content="{{ $pageUrl }}">
    <meta property="og:title" content="{{ $pageTitle }}">
    <meta property="og:description" content="{{ $pageDescription }}">
    <meta property="og:image" content="{{ $pageImage }}">
    <meta property="og:image:alt" content="{{ $pageTitle }}">
    <meta property="og:site_name" content="Tradexy">
    <meta property="og:image:width" content="1200">
    <meta property="og:image:height" content="630">

    <!-- Twitter -->
    <meta property="twitter:card" content="summary_large_image">
    <meta property="twitter:url" content="{{ $pageUrl }}">
    <meta property="twitter:title" content="{{ $pageTitle }}">
    <meta property="twitter:description" content="{{ $pageDescription }}">
    <meta property="twitter:image" content="{{ $pageImage }}">
    <!-- Favicon -->
    <link rel="icon" type="image/png" href="{{ asset('images/logo.png') }}" />

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600" rel="stylesheet" />

    <!-- Styles / Scripts -->
    <x-posthog />
    @vite(['resources/css/app.css', 'resources/js/app.js'])

</head>

<body class="bg-[#FDFDFC] dark:bg-[#0a0a0a] text-[#1b1b18] min-h-screen flex flex-col">
    <x-nav-bar />
    <main>
        {{ $slot }}
    </main>
    <footer class="w-full border-t border-gray-200 dark:border-[#1F1F1E] py-4 mt-auto bg-[#FDFDFC] dark:bg-[#0a0a0a]">
        <div
            class="max-w-7xl mx-auto px-6 flex flex-col md:flex-row justify-between items-center gap-4 text-sm text-gray-500 dark:text-gray-400">
            <div>© {{ date('Y') }} Tradexy — All rights reserved.</div>
            <div class="flex gap-6">
                <a href="#" class="hover:text-gray-900 dark:hover:text-white transition-colors">Privacy Policy</a>
                <a href="#" class="hover:text-gray-900 dark:hover:text-white transition-colors">Terms of Service</a>
                <a href="#" class="hover:text-gray-900 dark:hover:text-white transition-colors">Data Deletion</a>
            </div>
        </div>
    </footer>
    <x-toast />
    @auth
        <script type="module">
            if (window.Echo) {
                window.Echo.private("App.Models.User." + @js(auth()->id()))
                    .listen('.NewTradesFetched', (e) => {
                        // 1. Show the notification
                        if (window.showToast) {
                            window.showToast(e.message, 'success');
                        }

                        // 2. Trigger auto-reload if on Trades Index or Dashboard
                        const path = window.location.pathname;
                        if (path.includes('/trades') || path.includes('/dashboard')) {
                            setTimeout(() => {
                                window.location.reload();
                            }, 2000);
                        }
                    });

                // Market News Updates (Public Channel)
                window.Echo.channel('market-insights')
                    .listen('MarketNewsGenerated', (e) => {
                        if (window.showToast) {
                            window.showToast(e.message, 'info');
                        }

                        const path = window.location.pathname;
                        if (path.includes('/insights') || path.includes('/dashboard')) {
                            setTimeout(() => {
                                window.location.reload();
                            }, 2000);
                        }
                    });
            }
        </script>
    @endauth
    <script>
        function togglePassword(id) {
            const input = document.getElementById(id);
            const icon = document.getElementById(id + '-icon');
            if (input.type === 'password') {
                input.type = 'text';
                if (icon) {
                    icon.innerHTML = '<path stroke-linecap="round" stroke-linejoin="round" d="M3.98 8.223A10.477 10.477 0 0 0 1.934 12C3.226 16.338 7.244 19.5 12 19.5c.993 0 1.953-.138 2.863-.395M6.228 6.228A10.451 10.451 0 0 1 12 4.5c4.756 0 8.773 3.162 10.065 7.498a10.522 10.522 0 0 1-4.293 5.774M6.228 6.228 3 3m3.228 3.228 3.65 3.65m7.894 7.894L21 21m-3.228-3.228-3.65-3.65m0 0a3 3 0 1 0-4.243-4.243m4.242 4.242L9.88 9.88" />';
                }
            } else {
                input.type = 'password';
                if (icon) {
                    icon.innerHTML = '<path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 0 1 0-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178Z" /><path stroke-linecap="round" stroke-linejoin="round" d="M15 12.073c0-1.657-1.343-3-3-3s-3 1.343-3 3 1.343 3 3 3 3-1.343 3-3Z" />';
                }
            }
        }
    </script>
</body>

</html>