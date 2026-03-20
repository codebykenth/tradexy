@props(['src', 'alt' => 'Image', 'class' => '', 'object' => 'cover', 'fetchpriority' => 'auto'])

@php
    $finalUrl = $src;
    
    // Check if we need to swap for CDN (in case researchers/code use raw chart_picture instead of direct_chart_url)
    $cdnBase = config('filesystems.disks.gcs.url');
    if ($src && $cdnBase && str_contains($src, 'storage.googleapis.com')) {
        $finalUrl = str_replace('https://storage.googleapis.com', rtrim($cdnBase, '/'), $src);
    }
@endphp

<div class="relative overflow-hidden bg-base-200 {{ $class }}">
    {{-- Shimmer effect background --}}
    <div class="absolute inset-0 bg-gradient-to-r from-transparent via-base-300/30 to-transparent -translate-x-full animate-[shimmer_2s_infinite]"></div>
    
    <img 
        src="{{ $finalUrl }}" 
        alt="{{ $alt }}"
        loading="{{ $fetchpriority === 'high' ? 'eager' : 'lazy' }}"
        fetchpriority="{{ $fetchpriority }}"
        decoding="async"
        class="w-full h-full object-{{ $object }} opacity-0 transition-opacity duration-500"
        onload="this.classList.remove('opacity-0')"
        onerror="this.onerror=null; this.src='/images/placeholder.png'; this.classList.remove('opacity-0');"
        {{ $attributes->whereDoesntStartWith(['src', 'alt', 'class']) }}
    />
</div>

<style>
@keyframes shimmer {
    100% {
        transform: translateX(100%);
    }
}
</style>
