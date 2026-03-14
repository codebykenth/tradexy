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
                window.Echo.private(`App.Models.User.{{ auth()->id() }}`)
                    .listen('.NewTradesFetched', (e) => {
                        if (window.showToast) {
                            window.showToast(e.message, 'success');
                        }
                    });
            }
        </script>
    @endauth
</body>

</html>