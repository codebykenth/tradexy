@props([
    'title',
    'subtitle' => null
])

<div class="my-8">
    <h1 class="text-3xl font-bold text-gray-900 dark:text-white">{{$title}}</h1>
    <p class="text-gray-500 dark:text-gray-400 mt-1">
        {{ $subtitle }}
    </p>
</div>