<div class="border border-gray-300 rounded-lg overflow-x-auto">
    <div class="min-w-full">
        <table class="w-full">
            @isset($header)
                <thead>
                    <tr
                        class="border-b border-gray-300 bg-gray-100 h-10 [&>th:first-child]:pl-4 [&>th:last-child]:pr-4 uppercase text-sm text-gray-700">
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