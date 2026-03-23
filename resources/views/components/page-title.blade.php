@props([
    'title',
    'subtitle' => null
])

<div class="my-8">
    <h1 class="text-3xl font-bold text-base-content">{{$title}}</h1>
    <p class="text-base-content/60 mt-1">
        {{ $subtitle }}
    </p>
</div>