<x-layouts.admin title="User Directory">
    <div class="space-y-8">
        <!-- Header -->
        <div class="flex flex-col md:flex-row md:items-end justify-between gap-4">
            <div>
                <h1 class="text-4xl font-black tracking-tight text-base-content uppercase leading-none">User <span class="text-primary italic">Directory</span></h1>
                <p class="text-base-content/60 mt-2 font-medium">Manage registered accounts and track user connectivity.</p>
            </div>
        </div>

        <!-- content -->
        <div class="overflow-hidden rounded-3xl border border-base-300 bg-base-100 shadow-xl shadow-base-300/20">
            <table class="table w-full">
                <thead>
                    <tr class="bg-base-200/50 border-b border-base-300">
                        <th class="text-[10px] uppercase font-black tracking-widest py-5">User Details</th>
                        <th class="text-[10px] uppercase font-black tracking-widest py-5 text-center">Connection</th>
                        <th class="text-[10px] uppercase font-black tracking-widest py-5 text-center">Time Invested</th>
                        <th class="text-[10px] uppercase font-black tracking-widest py-5 text-center">Activity Status</th>
                        <th class="text-[10px] uppercase font-black tracking-widest py-5 text-center">Registration</th>
                        <th class="text-[10px] uppercase font-black tracking-widest py-5 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-base-200">
                    @foreach($users as $user)
                        <tr class="hover:bg-base-200/30 transition-colors group">
                            <td class="py-5">
                                <div class="flex items-center gap-4">
                                    <div class="avatar placeholder">
                                        <div class="bg-primary/10 text-primary rounded-xl w-12 h-12 font-black border border-primary/20">
                                            <span>{{ substr($user->name, 0, 1) }}</span>
                                        </div>
                                    </div>
                                    <div>
                                        <div class="font-black text-base-content flex items-center gap-2">
                                            {{ $user->name }}
                                            @if($user->is_admin)
                                                <span class="badge badge-primary badge-sm font-black uppercase tracking-widest text-[8px] italic shadow-sm shadow-primary/20 px-2 py-2">Master</span>
                                            @endif
                                        </div>
                                        <div class="text-[10px] font-bold opacity-40 uppercase tracking-widest">{{ $user->email }}</div>
                                    </div>
                                </div>
                            </td>
                            <td class="py-5 text-center">
                                @if($user->last_seen_at && $user->last_seen_at->diffInMinutes(now()) < 10)
                                    <div class="inline-flex items-center gap-1.5 px-3 py-1 bg-success/10 text-success rounded-full border border-success/20">
                                        <div class="w-1.5 h-1.5 rounded-full bg-current animate-pulse"></div>
                                        <span class="text-[10px] font-black uppercase tracking-widest">Online</span>
                                    </div>
                                @else
                                    <div class="inline-flex items-center gap-1.5 px-3 py-1 bg-base-300/30 text-base-content/40 rounded-full border border-base-300/50">
                                        <div class="w-1.5 h-1.5 rounded-full bg-current"></div>
                                        <span class="text-[10px] font-black uppercase tracking-widest">Offline</span>
                                    </div>
                                @endif
                            </td>
                            <td class="py-5 text-center">
                                <div class="flex flex-col items-center">
                                    <div class="text-xs font-black text-base-content">{{ $user->formatted_duration }}</div>
                                    <div class="flex items-center gap-1 opacity-20 text-[9px] font-bold uppercase tracking-widest mt-0.5">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="w-2.5 h-2.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                                        Time Invested
                                    </div>
                                </div>
                            </td>
                            <td class="py-5 text-center">
                                <div class="text-xs font-black uppercase tracking-tighter opacity-70">
                                    {{ $user->last_seen_at ? $user->last_seen_at->diffForHumans() : 'Never' }}
                                </div>
                                @if($user->last_login_at)
                                    <div class="text-[9px] font-bold opacity-30 mt-0.5 uppercase tracking-widest italic">Last Login: {{ $user->last_login_at->format('M d, H:i') }}</div>
                                @endif
                            </td>
                            <td class="py-5 text-center">
                                <div class="text-xs font-black opacity-70 italic">{{ $user->created_at->format('M d, Y') }}</div>
                            </td>
                            <td class="py-5 text-right">
                                <div class="flex justify-end gap-2 opacity-30 group-hover:opacity-100 transition-opacity">
                                    <a href="{{ route('admin.logs', ['user_id' => $user->id]) }}" 
                                       class="btn btn-square btn-xs btn-outline hover:btn-primary border-base-300"
                                       title="View User Activity">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" /></svg>
                                    </a>
                                    @if(!$user->is_admin)
                                        <button class="btn btn-square btn-xs btn-outline btn-error hover:btn-error border-base-300">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg>
                                        </button>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        
        <div class="mt-4">
            {{ $users->links() }}
        </div>
    </div>
</x-layouts.admin>
