<tr {{ $attributes->merge(['class' => 'border-b border-base-300 odd:bg-base-200 even:bg-base-100 hover:bg-base-300 transition-colors text-center h-12 [&>th:first-child]:pl-4 [&>td:last-child]:pr-4 cursor-pointer']) }}>
    {{ $slot }}
</tr>