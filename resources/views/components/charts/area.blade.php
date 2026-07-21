@props([
    'id',
    'series',
    'categories',
    'title',
    'color' => '#10B981', // Default emerald green
    'formatter' => 'usd', // 'usd' or 'number'
    'prefix' => '$'
])

<div class="bg-white dark:bg-[#141414] border border-gray-200 dark:border-[#1F1F1E] rounded-xl shadow-sm p-4 w-full h-full">
    <h3 class="text-lg font-semibold mb-2 text-gray-900 dark:text-gray-100">{{ $title }}</h3>
    <div id="{{ $id }}" class="w-full"></div>
</div>

<script>
    (function () {
        const chartId = @js($id);

        const render = () => {
            const target = document.querySelector('#' + chartId);
            if (!target) return;
            if (!window.ApexCharts) {
                console.error('ApexCharts is not globally defined in window.');
                return;
            }

            if (target._chartInstance) {
                target._chartInstance.destroy();
            }

            const isDarkMode = document.documentElement.getAttribute('data-theme') === 'dark' || document.documentElement.classList.contains('dark');
        const textColor = isDarkMode ? '#9CA3AF' : '#6B7280'; // gray-400 : gray-500
        const gridColor = isDarkMode ? '#1F2937' : '#E5E7EB'; // gray-800 : gray-200

        const options = {
            series: [{
                name: @js($title),
                data: @json($series)
            }],
            chart: {
                type: 'area',
                id: @js($id),
                height: 300,
                fontFamily: 'inherit',
                toolbar: { show: false },
                zoom: { enabled: false },
                background: 'transparent'
            },
            colors: [@js($color)],
            fill: {
                type: 'gradient',
                gradient: {
                    shadeIntensity: 1,
                    opacityFrom: 0.4,
                    opacityTo: 0.05,
                    stops: [0, 90, 100]
                }
            },
            dataLabels: { enabled: false },
            stroke: { curve: 'smooth', width: 2 },
            xaxis: {
                categories: @json($categories),
                tooltip: { enabled: false },
                axisBorder: { show: false },
                axisTicks: { show: false },
                labels: {
                    style: { colors: textColor },
                    hideOverlappingLabels: true,
                    rotate: -45,
                    rotateAlways: false,
                    trim: true
                },
                tickAmount: 10, // Force reasonable number of ticks to prevent label bunching
            },
            yaxis: {
                labels: {
                    formatter: function (val) {
                        @if($formatter === 'usd')
                            return @js($prefix) + val.toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 });
                        @else
                            return val;
                        @endif
                    },
                    style: { colors: textColor }
                }
            },
            grid: {
                borderColor: gridColor,
                strokeDashArray: 4,
                yaxis: { lines: { show: true } },
                xaxis: { lines: { show: false } },
                padding: { top: 0, right: 0, bottom: 0, left: 10 }
            },
            theme: {
                mode: isDarkMode ? 'dark' : 'light'
            },
            tooltip: {
                theme: isDarkMode ? 'dark' : 'light',
                y: {
                    formatter: function (val) {
                        @if($formatter === 'usd')
                            return @js($prefix) + val.toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 });
                        @else
                            return val;
                        @endif
                    }
                }
            }
        };

            const chart = new window.ApexCharts(target, options);
            chart.render();
            target._chartInstance = chart;
        };

        // Inline classic <script> re-executes on each Turbo body swap, so
        // calling render() immediately covers both cold loads and SPA visits.
        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', render, { once: true });
        } else {
            render();
        }
    })();
</script>
