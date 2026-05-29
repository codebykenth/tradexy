<x-layouts.app title="System Audit Logs">
    <div class="max-w-7xl mx-auto px-6 space-y-6 mb-8 mt-6">
        <x-admin-nav />
        
        <div class="space-y-8">
        <!-- Header -->
        <div class="flex flex-col md:flex-row md:items-end justify-between gap-4">
            <div>
                <h1 class="text-4xl font-black tracking-tight text-base-content uppercase leading-none">Audit <span class="text-primary italic">Logs</span></h1>
                <p class="text-base-content/60 mt-2 font-medium">Immutable chronological trail of system and user interactions.</p>
            </div>
        </div>

        <!-- content -->
        <div class="overflow-hidden rounded-3xl border border-base-300 bg-base-100 shadow-xl shadow-base-300/20">
            <table class="table w-full">
                <thead>
                    <tr class="bg-base-200/50 border-b border-base-300">
                        <th class="text-[10px] uppercase font-black tracking-widest py-5">Time Signal</th>
                        <th class="text-[10px] uppercase font-black tracking-widest py-5">User ID</th>
                        <th class="text-[10px] uppercase font-black tracking-widest py-5">Event</th>
                        <th class="text-[10px] uppercase font-black tracking-widest py-5">Activity Summary</th>
                        <th class="text-[10px] uppercase font-black tracking-widest py-5 text-right">Technical Data</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-base-200">
                    @foreach($logs as $log)
                        <tr class="hover:bg-base-200/30 transition-colors">
                            <td class="py-5">
                                <div class="font-mono text-xs font-black opacity-70">{{ $log->created_at->format('Y-m-d') }}</div>
                                <div class="font-mono text-[10px] opacity-40 uppercase tracking-widest">{{ $log->created_at->format('H:i:s') }}</div>
                            </td>
                            <td class="py-5">
                                <div class="flex items-center gap-3">
                                    <div class="avatar placeholder">
                                        <div class="bg-neutral text-neutral-content rounded-lg w-8 h-8 font-bold text-xs uppercase">
                                            <span>{{ substr($log->user->name ?? 'System', 0, 1) }}</span>
                                        </div>
                                    </div>
                                    <div class="font-black text-sm uppercase italic">{{ $log->user->name ?? 'System' }}</div>
                                </div>
                            </td>
                            <td class="py-5">
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
                            <td class="py-5 text-xs font-medium opacity-70 italic">{{ $log->description }}</td>
                            <td class="py-5 text-right">
                                <div class="text-[10px] font-mono leading-tight">
                                    <div class="font-black text-primary opacity-70">{{ $log->ip_address }}</div>
                                    <div class="opacity-30 truncate max-w-[200px] mt-0.5 ml-auto" title="{{ $log->user_agent }}">
                                        {{ $log->user_agent }}
                                    </div>
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <div class="mt-4">
            {{ $logs->links() }}
        </div>
    </div>
</x-layouts.app>
