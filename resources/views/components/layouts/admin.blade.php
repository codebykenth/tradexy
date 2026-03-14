<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full bg-base-200">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ $title ?? 'Admin Dashboard' }} | Tradexy</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600,700,800" rel="stylesheet" />

    <!-- Styles / Scripts -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="h-full font-sans antialiased text-base-content overflow-hidden">
    <div class="drawer lg:drawer-open h-full">
        <input id="admin-drawer" type="checkbox" class="drawer-toggle" />
        
        <div class="drawer-content flex flex-col h-full overflow-hidden bg-base-200/50">
            <!-- Topbar (Mobile) -->
            <div class="lg:hidden navbar bg-base-100 border-b border-base-300 px-4">
                <div class="flex-none">
                    <label for="admin-drawer" class="btn btn-square btn-ghost">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" class="inline-block w-6 h-6 stroke-current"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path></svg>
                    </label>
                </div>
                <div class="flex-1">
                    <span class="text-xl font-black tracking-tighter uppercase ml-2">Tradexy <span class="text-primary">Admin</span></span>
                </div>
                <div class="flex-none">
                    <div class="avatar placeholder">
                        <div class="bg-neutral text-neutral-content rounded-full w-8">
                            <span class="text-xs">{{ substr(Auth::user()->name, 0, 1) }}</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Main Content Area -->
            <main class="flex-1 overflow-y-auto p-4 md:p-8 lg:p-10">
                <div class="max-w-7xl mx-auto h-full">
                    {{ $slot }}
                </div>
            </main>
        </div>

        <!-- Sidebar -->
        <div class="drawer-side z-50">
            <label for="admin-drawer" class="drawer-overlay"></label>
            <div class="w-72 min-h-full bg-base-100 border-r border-base-300 flex flex-col">
                <!-- Branding -->
                <div class="p-8 pb-4">
                    <div class="flex items-center gap-3">
                         <img src="{{ asset('images/logo.png') }}" alt="Logo" class="w-10 h-10 rounded-xl shadow-lg shadow-primary/20">
                         <div>
                            <h2 class="text-2xl font-black tracking-tighter uppercase leading-none">Tradexy</h2>
                            <span class="text-[10px] font-bold uppercase tracking-[0.2em] text-primary">Control Panel</span>
                         </div>
                    </div>
                </div>

                <!-- Navigation -->
                <nav class="flex-1 px-4 py-6 space-y-1">
                    <div class="px-4 mb-2">
                        <span class="text-[10px] font-black uppercase tracking-[0.2em] text-base-content/40 italic">Navigation</span>
                    </div>
                    
                    <a href="{{ route('admin.dashboard') }}" 
                       @class(['group flex items-center px-4 py-3 text-sm font-bold rounded-xl transition-all duration-200', 'bg-primary text-primary-content shadow-lg shadow-primary/20' => request()->routeIs('admin.dashboard'), 'hover:bg-base-200' => !request()->routeIs('admin.dashboard')])>
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 mr-3" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z" /></svg>
                        Dashboard
                    </a>

                    <a href="{{ route('admin.users') }}" 
                       @class(['group flex items-center px-4 py-3 text-sm font-bold rounded-xl transition-all duration-200', 'bg-primary text-primary-content shadow-lg shadow-primary/20' => request()->routeIs('admin.users'), 'hover:bg-base-200' => !request()->routeIs('admin.users')])>
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 mr-3" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" /></svg>
                        User Directory
                    </a>

                    <a href="{{ route('admin.logs') }}" 
                       @class(['group flex items-center px-4 py-3 text-sm font-bold rounded-xl transition-all duration-200', 'bg-primary text-primary-content shadow-lg shadow-primary/20' => request()->routeIs('admin.logs'), 'hover:bg-base-200' => !request()->routeIs('admin.logs')])>
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 mr-3" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" /></svg>
                        System Audit Logs
                    </a>

                    <div class="px-4 mt-8 mb-2">
                        <span class="text-[10px] font-black uppercase tracking-[0.2em] text-base-content/40 italic">System</span>
                    </div>

                    <a href="/" class="group flex items-center px-4 py-3 text-sm font-bold rounded-xl transition-all duration-200 hover:bg-base-200">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 mr-3" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" /></svg>
                        Return to App
                    </a>
                </nav>

                <!-- User Profile Section -->
                <div class="p-6 border-t border-base-300">
                    <div class="flex items-center gap-3">
                        <div class="avatar online">
                            <div class="w-10 rounded-xl bg-neutral text-neutral-content flex items-center justify-center font-black">
                                {{ substr(Auth::user()->name, 0, 1) }}
                            </div>
                        </div>
                        <div class="flex-1 overflow-hidden">
                            <h4 class="text-sm font-black truncate">{{ Auth::user()->name }}</h4>
                            <p class="text-[10px] uppercase font-bold text-primary tracking-widest">Administrator</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Toast Component -->
    <x-toast />
</body>
</html>
