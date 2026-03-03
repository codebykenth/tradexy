{{-- Global toast notification component.
Reads Laravel flash session keys ('success', 'error', 'info', 'warning')
and renders a DaisyUI toast that auto-dismisses after 4 seconds.
Include once in the app layout — no props needed. --}}

@php
    $toasts = [];
    if (session('success'))
        $toasts[] = ['type' => 'alert-success', 'icon' => 'M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z', 'message' => session('success')];
    if (session('error'))
        $toasts[] = ['type' => 'alert-error', 'icon' => 'M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z', 'message' => session('error')];
    if (session('info'))
        $toasts[] = ['type' => 'alert-info', 'icon' => 'M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z', 'message' => session('info')];
    if (session('warning'))
        $toasts[] = ['type' => 'alert-warning', 'icon' => 'M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126ZM12 15.75h.007v.008H12v-.008Z', 'message' => session('warning')];
@endphp

@if (count($toasts) > 0)
    <div class="toast toast-end toast-top z-50" id="toast-container">
        @foreach ($toasts as $toast)
            <div class="alert {{ $toast['type'] }} shadow-lg transition-all duration-300">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"
                    class="size-6 shrink-0">
                    <path stroke-linecap="round" stroke-linejoin="round" d="{{ $toast['icon'] }}" />
                </svg>
                <span>{{ $toast['message'] }}</span>
                <button type="button" class="btn btn-ghost btn-xs" onclick="this.closest('.alert').remove()">✕</button>
            </div>
        @endforeach
    </div>

    <script>
        // Auto-dismiss initial toasts
        setTimeout(() => {
            const container = document.getElementById('toast-container');
            if (container) {
                container.style.opacity = '0';
                container.style.transition = 'opacity 0.5s ease';
                setTimeout(() => container.remove(), 500);
            }
        }, 4000);
    </script>
@endif

<script>
    window.showToast = function (message, type = 'success') {
        let container = document.getElementById('toast-container');
        if (!container) {
            container = document.createElement('div');
            container.id = 'toast-container';
            container.className = 'toast toast-end toast-top z-50';
            document.body.appendChild(container);
        }

        const alertDiv = document.createElement('div');
        const alertClass = type === 'success' ? 'alert-success' : (type === 'error' ? 'alert-error' : 'alert-info');
        const iconPath = type === 'success'
            ? 'M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z'
            : (type === 'error' ? 'M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z' : 'M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z');

        alertDiv.className = `alert ${alertClass} shadow-lg transition-all duration-300 transform translate-y-2 opacity-0`;
        alertDiv.innerHTML = `
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-6 shrink-0">
                <path stroke-linecap="round" stroke-linejoin="round" d="${iconPath}" />
            </svg>
            <span>${message}</span>
            <button type="button" class="btn btn-ghost btn-xs" onclick="this.closest('.alert').remove()">✕</button>
        `;

        container.appendChild(alertDiv);

        // Animate in
        requestAnimationFrame(() => {
            alertDiv.classList.remove('translate-y-2', 'opacity-0');
        });

        // Auto-remove
        setTimeout(() => {
            alertDiv.classList.add('opacity-0');
            setTimeout(() => alertDiv.remove(), 500);
        }, 4000);
    };
</script>