<div class="border border-base-300 rounded-lg overflow-x-auto">
    <div class="min-w-full">
        <table class="w-full">
            @isset($header)
                <thead>
                    <tr
                        class="border-b border-base-300 bg-base-200 h-10 [&>th]:px-2 [&>th]:sm:px-4 [&>th:first-child]:pl-4 [&>th:last-child]:pr-4 uppercase text-xs sm:text-sm text-base-content/70">
                        {{ $header }}
                    </tr>
                </thead>
            @endisset
            <tbody>
                {{ $slot }}
            </tbody>
        </table>
    </div>
</div>