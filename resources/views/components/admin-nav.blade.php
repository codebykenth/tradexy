<div class="flex items-center gap-2 mb-6 border-b border-base-200 pb-4 overflow-x-auto">
    <a href="{{ route('admin.dashboard') }}" @class([
        'btn btn-sm rounded-full font-bold',
        'btn-primary' => request()->routeIs('admin.dashboard'),
        'btn-ghost' => !request()->routeIs('admin.dashboard')
    ])>
        Dashboard
    </a>
    <a href="{{ route('admin.users') }}" @class([
        'btn btn-sm rounded-full font-bold',
        'btn-primary' => request()->routeIs('admin.users'),
        'btn-ghost' => !request()->routeIs('admin.users')
    ])>
        User Directory
    </a>
    <a href="{{ route('admin.logs') }}" @class([
        'btn btn-sm rounded-full font-bold',
        'btn-primary' => request()->routeIs('admin.logs'),
        'btn-ghost' => !request()->routeIs('admin.logs')
    ])>
        Audit Logs
    </a>
</div>