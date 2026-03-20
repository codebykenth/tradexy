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
        onerror="this.onerror=null; this.src='data:image/svg+xml;charset=UTF-8,%3Csvg xmlns=%22http://www.w3.org/2000/svg%22 width=%22100%22 height=%22100%22 viewBox=%220 0 100 100%22%3E%3Crect width=%22100%22 height=%22100%22 fill=%22%23f3f4f6%22/%3E%3Cpath stroke=%22%239ca3af%22 stroke-width=%221.5%22 d=%22M30 40l20 20 20-20%22 fill=%22none%22/%3E%3Ctext x=%2250%22 y=%2270%22 font-family=%22sans-serif%22 font-size=%228%22 text-anchor=%22middle%22 fill=%22%239ca3af%22%3ENo Image%3C/text%3E%3C/svg%3E'; this.classList.remove('opacity-0');"
        {{ $attributes->whereDoesntStartWith(['src', 'alt', 'class']) }}
    />
</div>