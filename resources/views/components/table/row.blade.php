<tr {{ $attributes->merge(['class' => 'border-b border-gray-300 odd:bg-gray-100 even:bg-white hover:bg-gray-200 transition-colors text-center h-12 [&>th:first-child]:pl-4 [&>td:last-child]:pr-4 cursor-pointer']) }}>
    {{ $slot }}
</tr>