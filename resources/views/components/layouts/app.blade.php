<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <!-- Reverb Dynamic Config for frontend without needing Vite rebuilds -->
    <meta name="reverb-app-key" content="{{ config('broadcasting.connections.reverb.key') }}">
    <meta name="reverb-host" content="{{ config('broadcasting.connections.reverb.options.host') }}">
    <meta name="reverb-port" content="{{ config('broadcasting.connections.reverb.options.port') }}">
    <meta name="reverb-scheme" content="{{ config('broadcasting.connections.reverb.options.scheme') }}">

    <title>{{ $title ?? config('app.name') }}</title>

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