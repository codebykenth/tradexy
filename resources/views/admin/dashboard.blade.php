<x-layouts.admin title="Admin Command Center">
    <div class="space-y-10">
        <!-- Header -->
        <div class="flex flex-col md:flex-row md:items-end justify-between gap-4">
            <div>
                <div class="flex items-center gap-2 mb-2">
                    <span class="badge badge-primary badge-outline font-black uppercase tracking-widest text-[10px]">v2.1.0-alpha</span>
                    @if(app()->isDownForMaintenance())
                        <span class="badge badge-warning gap-1 font-black uppercase tracking-widest text-[10px] py-3">
                            <div class="w-1.5 h-1.5 rounded-full bg-white animate-pulse"></div>
                            Maintenance
                        </span>
                    @else
                        <span class="badge badge-success gap-1 font-black uppercase tracking-widest text-[10px] py-3">
                            <div class="w-1.5 h-1.5 rounded-full bg-white animate-pulse"></div>
                            Live
                        </span>
                    @endif
                </div>
                <h1 class="text-4xl font-black tracking-tight text-base-content uppercase leading-none">Command <span class="text-primary italic">Center</span></h1>
                <p class="text-base-content/60 mt-2 font-medium">Real-time system oversight and user engagement tracking.</p>
            </div>
            <div class="flex items-center gap-3">
                <button class="btn btn-primary shadow-lg shadow-primary/20 font-black uppercase tracking-widest text-xs px-6">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" /></svg>
                    Export Logs
                </button>
            </div>
        </div>

        @if(app()->isDownForMaintenance())
        <div class="alert alert-warning shadow-lg border-2 border-warning/50 rounded-3xl p-6">
            <svg xmlns="http://www.w3.org/2000/svg" class="stroke-current shrink-0 h-8 w-8" fill="none" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" /></svg>
            <div class="flex-1">
                <h3 class="font-black uppercase tracking-tight text-lg">System is in Maintenance Mode</h3>
                <div class="text-xs font-medium opacity-70">Public access is currently restricted. Only administrators with a bypass cookie can see the site.</div>
            </div>
            <form action="{{ route('admin.maintenance.toggle') }}" method="POST">
                @csrf
                <button class="btn btn-sm btn-outline font-black uppercase tracking-widest text-[10px]">Take Site Live</button>
            </form>
        </div>
        @endif

        <!-- Stats Grid -->
        <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-4 gap-6">
            <!-- Total Users Card -->
            <div class="card bg-base-100 shadow-xl shadow-base-300/50 border border-base-200 overflow-hidden group hover:border-primary/30 transition-all duration-300">
                <div class="card-body p-6 flex-row items-center gap-6">
                    <div class="flex-1">
                        <p class="text-[10px] font-black uppercase tracking-[0.2em] text-base-content/40 mb-1">Total Users</p>
                        <h3 class="text-3xl font-black tracking-tighter">{{ number_format($totalUsers) }}</h3>
                        <div class="flex items-center gap-1 mt-1">
                            <span class="text-xs font-bold text-success">+{{ $newUsersThisWeek }}</span>
                            <span class="text-[10px] font-bold text-base-content/30 uppercase tracking-tighter">new this week</span>
                        </div>
                    </div>
                    <div class="w-14 h-14 rounded-2xl bg-primary/5 flex items-center justify-center text-primary group-hover:scale-110 transition-transform duration-500">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-8 h-8" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" /></svg>
                    </div>
                </div>
            </div>

            <!-- Active Today Card -->
            <div class="card bg-base-100 shadow-xl shadow-base-300/50 border border-base-200 overflow-hidden group hover:border-secondary/30 transition-all duration-300">
                <div class="card-body p-6 flex-row items-center gap-6">
                    <div class="flex-1">
                        <p class="text-[10px] font-black uppercase tracking-[0.2em] text-base-content/40 mb-1">Active Today</p>
                        <h3 class="text-3xl font-black tracking-tighter">{{ number_format($activeToday) }}</h3>
                        <div class="flex items-center gap-1 mt-1">
                            <span class="text-xs font-bold text-secondary">{{ $activeNow }}</span>
                            <span class="text-[10px] font-bold text-base-content/30 uppercase tracking-tighter">currently online</span>
                        </div>
                    </div>
                    <div class="w-14 h-14 rounded-2xl bg-secondary/5 flex items-center justify-center text-secondary group-hover:scale-110 transition-transform duration-500">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-8 h-8" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" /></svg>
                    </div>
                </div>
            </div>

            <!-- Journal Entries Card -->
            <div class="card bg-base-100 shadow-xl shadow-base-300/50 border border-base-200 overflow-hidden group hover:border-accent/30 transition-all duration-300">
                <div class="card-body p-6 flex-row items-center gap-6">
                    <div class="flex-1">
                        <p class="text-[10px] font-black uppercase tracking-[0.2em] text-base-content/40 mb-1">Journal Entries</p>
                        <h3 class="text-3xl font-black tracking-tighter">{{ number_format($totalTrades) }}</h3>
                        <div class="flex items-center gap-1 mt-1">
                            <span class="text-xs font-bold text-accent">+{{ $tradesToday }}</span>
                            <span class="text-[10px] font-bold text-base-content/30 uppercase tracking-tighter">added today</span>
                        </div>
                    </div>
                    <div class="w-14 h-14 rounded-2xl bg-accent/5 flex items-center justify-center text-accent group-hover:scale-110 transition-transform duration-500">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-8 h-8" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" /></svg>
                    </div>
                </div>
            </div>

            <!-- Data Density Card -->
            <div class="card bg-base-100 shadow-xl shadow-base-300/50 border border-base-200 overflow-hidden group hover:border-info/30 transition-all duration-300">
                <div class="card-body p-6 flex-row items-center gap-6">
                    <div class="flex-1">
                        <p class="text-[10px] font-black uppercase tracking-[0.2em] text-base-content/40 mb-1">Scale Index</p>
                        <h3 class="text-3xl font-black tracking-tighter">{{ number_format($totalBalances) }}</h3>
                        <div class="flex items-center gap-1 mt-1">
                            <span class="text-[10px] font-bold text-base-content/30 uppercase tracking-tighter">total data points</span>
                        </div>
                    </div>
                    <div class="w-14 h-14 rounded-2xl bg-info/5 flex items-center justify-center text-info group-hover:scale-110 transition-transform duration-500">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-8 h-8" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 7v10c0 2.21 3.582 4 8 4s8-1.79 8-4V7M4 7c0 2.21 3.582 4 8 4s8-1.79 8-4M4 7c0-2.21 3.582-4 8-4s8 1.79 8 4m0 5c0 2.21-3.582 4-8 4s-8-1.79-8-4" /></svg>
                    </div>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            <!-- Recent Activity -->
            <div class="lg:col-span-2 space-y-6">
                <div class="flex justify-between items-end border-b border-base-300 pb-4">
                    <div>
                        <h2 class="text-2xl font-black uppercase tracking-tight leading-none italic">Activity <span class="text-primary">Stream</span></h2>
                        <p class="text-xs text-base-content/50 mt-1 font-bold">Latest authentication and system events.</p>
                    </div>
                    <a href="{{ route('admin.logs') }}" class="btn btn-xs btn-ghost gap-2 font-black uppercase tracking-widest opacity-60 hover:opacity-100">
                        View Full History
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" /></svg>
                    </a>
                </div>

                <div class="overflow-hidden rounded-3xl border border-base-300 bg-base-100 shadow-xl shadow-base-300/20">
                    <table class="table w-full">
                        <thead>
                            <tr class="bg-base-200/50 border-b border-base-300">
                                <th class="text-[10px] uppercase font-black tracking-widest py-4">Identity</th>
                                <th class="text-[10px] uppercase font-black tracking-widest py-4">Event Type</th>
                                <th class="text-[10px] uppercase font-black tracking-widest py-4">System Details</th>
                                <th class="text-[10px] uppercase font-black tracking-widest py-4 text-right">Timestamp</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-base-200">
                            @foreach($recentLogs as $log)
                                <tr class="hover:bg-base-200/30 transition-colors">
                                    <td class="py-4">
                                        <div class="flex items-center gap-3">
                                            <div class="avatar placeholder">
                                                <div class="bg-neutral text-neutral-content rounded-xl w-10 h-10 font-bold">
                                                    <span>{{ substr($log->user->name, 0, 1) }}</span>
                                                </div>
                                            </div>
                                            <div>
                                                <div class="font-black text-sm">{{ $log->user->name }}</div>
                                                <div class="text-[10px] font-mono opacity-50">{{ $log->ip_address }}</div>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="py-4">
                                        <div @class([
                                            'px-3 py-1 rounded-full text-[10px] font-black uppercase tracking-widest inline-flex items-center gap-1.5',
                                            'bg-success/10 text-success border border-success/20' => $log->action === 'login',
                                            'bg-error/10 text-error border border-error/20' => $log->action === 'logout',
                                            'bg-info/10 text-info border border-info/20' => !in_array($log->action, ['login', 'logout']),
                                        ])>
                                            <div class="w-1 h-1 rounded-full bg-current"></div>
                                            {{ $log->action }}
                                        </div>
                                    </td>
                                    <td class="py-4 text-xs font-medium opacity-70">{{ $log->description }}</td>
                                    <td class="py-4 text-right">
                                        <span class="text-xs font-mono opacity-50 italic">{{ $log->created_at->diffForHumans() }}</span>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Sidebar Info -->
            <div class="space-y-8">
                <!-- Health Card -->
                 <div class="card bg-base-100 shadow-xl shadow-base-300/20 border border-base-300 overflow-hidden">
                    <div class="bg-base-200/50 px-6 py-4 border-b border-base-200">
                         <h3 class="text-sm font-black uppercase tracking-widest italic">System <span class="text-primary">Health</span></h3>
                    </div>
                    <div class="card-body p-6 gap-5">
                        <div class="flex justify-between items-center">
                            <span class="text-xs font-bold opacity-40 uppercase tracking-widest">Environment</span>
                            <span class="badge badge-outline font-black text-[10px] uppercase tracking-widest">{{ app()->environment() }}</span>
                        </div>
                         <div class="flex justify-between items-center">
                            <span class="text-xs font-bold opacity-40 uppercase tracking-widest">PHP Instance</span>
                            <span class="text-xs font-black italic">{{ PHP_VERSION }}</span>
                        </div>
                        <div class="divider my-0 opacity-10"></div>
                        <div class="flex justify-between items-center">
                             <span class="text-xs font-bold opacity-40 uppercase tracking-widest">Web Server</span>
                             <span class="text-xs font-black">Nginx / Docker</span>
                        </div>
                    </div>
                 </div>

                 <!-- Quick Actions -->
                 <div @class([
                    'card shadow-2xl overflow-hidden relative',
                    'bg-warning text-warning-content shadow-warning/30' => app()->isDownForMaintenance(),
                    'bg-primary text-primary-content shadow-primary/30' => !app()->isDownForMaintenance(),
                 ])>
                    <div class="absolute top-0 right-0 p-4 opacity-10">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-20 h-20" fill="currentColor" viewBox="0 0 24 24"><path d="M12 1L3 5v6c0 5.55 3.84 10.74 9 12 5.16-1.26 9-6.45 9-12V5l-9-4zm0 10.99h7c-.53 4.12-3.28 7.79-7 8.94V12H5V6.3l7-3.11v8.8z"/></svg>
                    </div>
                    <div class="card-body p-8 relative z-10">
                        <h3 class="text-lg font-black uppercase tracking-widest italic leading-none mb-1">
                            {{ app()->isDownForMaintenance() ? 'System Locked' : 'Maintenance' }}
                        </h3>
                        <p class="text-[10px] font-bold uppercase tracking-widest opacity-70 mb-6">Internal System Protocols</p>
                        
                        <div class="space-y-3">
                            <form action="{{ route('admin.maintenance.toggle') }}" method="POST">
                                @csrf
                                <button type="submit" @class([
                                    'btn btn-sm btn-block border-none font-black uppercase tracking-widest text-[10px] py-4 shadow-lg',
                                    'bg-white text-warning hover:bg-white/90' => app()->isDownForMaintenance(),
                                    'bg-white text-primary hover:bg-white/90' => !app()->isDownForMaintenance(),
                                ])>
                                    {{ app()->isDownForMaintenance() ? 'Take Site Live' : 'Enable Maintenance Mode' }}
                                </button>
                            </form>
                            
                            <form action="{{ route('admin.cache.flush') }}" method="POST">
                                @csrf
                                <button type="submit" class="btn btn-sm btn-block bg-white/10 hover:bg-white/20 border-white/10 text-white font-black uppercase tracking-widest text-[10px] py-4">
                                    Flush System Cache
                                </button>
                            </form>
                            <button class="btn btn-sm btn-block bg-white/10 hover:bg-white/20 border-white/10 text-white font-black uppercase tracking-widest text-[10px] py-4">
                                Force Log Rotation
                            </button>
                        </div>
                    </div>
                 </div>
            </div>
        </div>
    </div>
</x-layouts.admin>
