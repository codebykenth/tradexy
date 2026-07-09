<tr {{ $attributes->merge(['class' => 'border-b border-base-300 odd:bg-base-200 even:bg-base-100 hover:bg-base-300 transition-colors text-center h-12 [&>th]:px-2 [&>th]:sm:px-4 [&>td]:px-2 [&>td]:sm:px-4 [&>th:first-child]:pl-4 [&>td:last-child]:pr-4 cursor-pointer']) }}>
    {{ $slot }}
</tr>