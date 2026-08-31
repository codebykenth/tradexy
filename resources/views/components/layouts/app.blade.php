<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" prefix="og: https://ogp.me/ns#" class="scroll-smooth">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    @if (config('app.realtime_enabled'))
        <meta name="realtime-enabled" content="1">
        <meta name="pusher-app-key" content="{{ config('broadcasting.connections.pusher.key') }}">
        <meta name="pusher-app-cluster" content="{{ config('broadcasting.connections.pusher.options.cluster') }}">
    @endif

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
    <meta name="theme-color" content="#2563eb">
    
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

    <!-- PWA -->
    <link rel="manifest" href="/manifest.json">
    <link rel="apple-touch-icon" href="/icons/icon-192x192.png">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
    <meta name="apple-mobile-web-app-title" content="Tradexy">
    <meta name="mobile-web-app-capable" content="yes">

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link rel="preconnect" href="https://storage.googleapis.com" crossorigin>
    <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700,800,900" rel="stylesheet" />

    <!-- Styles / Scripts -->
    <x-posthog />
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <!-- Turbo: reload when any tracked asset hash changes -->
    <meta name="turbo-cache-control" content="no-preview">
    <meta name="turbo-refresh-method" content="morph">
    <meta name="turbo-refresh-scroll" content="preserve">

    <!-- Dark Mode Support -->
    <script>
        (function() {
            const stored = localStorage.getItem('theme');
            if (stored) {
                document.documentElement.setAttribute('data-theme', stored);
            } else {
                const prefersDark = window.matchMedia('(prefers-color-scheme: dark)').matches;
                document.documentElement.setAttribute('data-theme', prefersDark ? 'dark' : 'light');
            }
        })();
    </script>
    
    <script async src="https://pagead2.googlesyndication.com/pagead/js/adsbygoogle.js?client=ca-pub-2027977096641438"
     crossorigin="anonymous"></script>
</head>

<body class="bg-base-100 text-base-content min-h-screen flex flex-col">
    @php
        $isGuestTopNavPage = !auth()->check() && request()->routeIs(
            'login',
            'register',
            'forgot-password',
            'password.reset'
        );
        $isLandingGuest = request()->path() === '/' && !auth()->check();
        $isLoginPage = request()->routeIs('login');
        $isRegisterPage = request()->routeIs('register');
    @endphp

    @if($isLandingGuest || $isGuestTopNavPage)
        <header id="landing-top-nav" class="fixed inset-x-0 top-0 z-50 transition-transform duration-300 ease-out">
            <div class="mx-auto max-w-7xl px-4 sm:px-6 pt-4">
                <div class="navbar rounded-2xl border border-base-300 bg-base-100/90 backdrop-blur-md shadow-sm">
                    <div class="navbar-start">
                        <a href="{{ url('/') }}" class="flex items-center gap-3 px-2">
                            <img src="{{ asset('images/logo.png') }}" alt="Logo" class="h-8 w-8 rounded-lg shadow-sm">
                            <span class="font-black text-xl tracking-tight">{{ config('app.name', 'Tradexy') }}</span>
                        </a>
                    </div>
                    @if($isLandingGuest)
                        <div class="navbar-center hidden md:flex">
                            <ul class="menu menu-horizontal px-1 font-medium">
                                <li><a href="/#features">Features</a></li>
                                <li><a href="/#how-it-works">How it Works</a></li>
                                <li><a href="/#why-tradexy">Why Tradexy</a></li>
                            </ul>
                        </div>
                    @endif
                    <div class="navbar-end gap-2">
                        @if($isLandingGuest)
                            <a href="{{ route('login') }}" class="btn btn-ghost btn-sm">Log in</a>
                            <a href="{{ route('register') }}" class="btn btn-primary btn-sm">Get Started</a>
                        @elseif($isLoginPage)
                            <a href="{{ route('register') }}" class="btn btn-primary btn-sm">Get Started</a>
                        @elseif($isRegisterPage)
                            <a href="{{ route('login') }}" class="btn btn-ghost btn-sm">Log in</a>
                        @else
                            <a href="{{ route('login') }}" class="btn btn-ghost btn-sm">Log in</a>
                        @endif
                    </div>
                </div>
            </div>
        </header>

        <main class="flex-1 pt-24">
            {{ $slot }}
        </main>

        <footer class="w-full border-t border-base-200 py-4 mt-auto bg-base-100">
            <div class="max-w-7xl mx-auto px-6 flex flex-col md:flex-row justify-between items-center gap-4 text-sm text-base-content/60">
                <div>© {{ date('Y') }} Tradexy — All rights reserved.</div>
                <div class="flex gap-6">
                    <a href="{{ route('privacy') }}" class="hover:text-base-content transition-colors">Privacy Policy</a>
                    <a href="{{ route('terms') }}" class="hover:text-base-content transition-colors">Terms of Service</a>
                    <a href="{{ route('deletion') }}" class="hover:text-base-content transition-colors">Data Deletion</a>
                </div>
            </div>
        </footer>
    @else
    <div id="app-drawer" class="drawer lg:drawer-open min-h-screen group">
        <script>
            if (localStorage.getItem('sidebar-collapsed') === 'true') {
                document.getElementById('app-drawer').classList.add('sidebar-collapsed');
            }
        </script>
        <input id="main-drawer" type="checkbox" class="drawer-toggle" />
        
        <div class="drawer-content flex flex-col min-h-screen relative transition-all duration-300">
            <!-- Mobile Header (Only for true mobile screens) -->
            <div id="top-header" class="lg:hidden flex items-center justify-between p-4 border-b border-base-200 bg-base-100/80 backdrop-blur-md sticky top-0 z-30">
                <div class="flex items-center gap-3">
                    <img src="{{ asset('images/logo.png') }}" alt="Logo" class="h-8 w-8 rounded-lg shadow-sm">
                    <span class="font-black text-xl tracking-tight">{{ config('app.name', 'Tradexy') }}</span>
                </div>
                <div class="flex items-center gap-2">
                    <label for="main-drawer" class="btn btn-ghost btn-circle btn-sm">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" /></svg>
                    </label>
                </div>
            </div>

            <main class="flex-1">
                {{ $slot }}
            </main>
            
            <footer class="w-full border-t border-base-200 py-4 mt-auto bg-base-100">
                <div class="max-w-7xl mx-auto px-6 flex flex-col md:flex-row justify-between items-center gap-4 text-sm text-base-content/60">
                    <div>© {{ date('Y') }} Tradexy — All rights reserved.</div>
                    <div class="flex gap-6">
                        <a href="{{ route('privacy') }}" class="hover:text-base-content transition-colors">Privacy Policy</a>
                        <a href="{{ route('terms') }}" class="hover:text-base-content transition-colors">Terms of Service</a>
                        <a href="{{ route('deletion') }}" class="hover:text-base-content transition-colors">Data Deletion</a>
                    </div>
                </div>
            </footer>
        </div> 

        <div class="drawer-side z-40 overflow-visible">
            <label for="main-drawer" aria-label="close sidebar" class="drawer-overlay"></label>
            <x-nav-bar />
        </div>
    </div>
    @endif
    <x-toast />
    <x-terms-modal />
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
    @if($isLandingGuest)
        <script>
            (function () {
                const nav = document.getElementById('landing-top-nav');
                if (!nav) return;

                let lastY = window.scrollY;
                const threshold = 12;

                const updateNav = () => {
                    const currentY = window.scrollY;
                    if (currentY <= 8) {
                        nav.classList.remove('-translate-y-full');
                    } else if (currentY > lastY + threshold) {
                        nav.classList.add('-translate-y-full');
                    } else if (currentY < lastY - threshold) {
                        nav.classList.remove('-translate-y-full');
                    }
                    lastY = currentY;
                };

                window.addEventListener('scroll', updateNav, { passive: true });
                updateNav();
            })();
        </script>
    @endif

    <!-- Service Worker Registration -->
    @production
    <script>
        if ('serviceWorker' in navigator) {
            window.addEventListener('load', () => {
                navigator.serviceWorker.register('/sw.js', { scope: '/' })
                    .then((reg) => {
                        // Auto-update on new version
                        reg.addEventListener('updatefound', () => {
                            const newWorker = reg.installing;
                            if (newWorker) {
                                newWorker.addEventListener('statechange', () => {
                                    if (newWorker.state === 'activated' && navigator.serviceWorker.controller) {
                                        console.log('[SW] New version available — will activate on next visit.');
                                    }
                                });
                            }
                        });
                    })
                    .catch((err) => console.warn('[SW] Registration failed:', err));
            });
        }
    </script>
    @endproduction
</body>

</html>