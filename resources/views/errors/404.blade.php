<x-layouts.app title="Page Not Found - Tradexy">
    <div class="min-h-[70vh] flex flex-col items-center justify-center px-6 py-24 sm:py-32 lg:px-8">
        <div class="text-center">
            <p class="text-6xl font-semibold text-primary">404</p>
            <h1 class="mt-4 text-3xl font-bold tracking-tight text-gray-900 sm:text-5xl">Page not found</h1>
            <p class="mt-6 text-base leading-7 text-gray-600">Sorry, we couldn’t find the page you’re looking for.</p>
            <div class="mt-10 flex items-center justify-center gap-x-6">
                <a href="{{ url('/') }}" class="btn btn-primary">
                    Go back home
                </a>
                <a href="javascript:history.back()" class="btn btn-ghost">
                    Go back
                </a>
            </div>
        </div>
    </div>
</x-layouts.app>