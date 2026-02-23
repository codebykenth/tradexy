@if(isset($trade) && $trade->chart_picture)
    <div id="chart-preview-container" class="relative inline-block mt-4 mb-4">
        <img src="{{ $trade->direct_chart_url ?? '' }}" alt="Chart Screenshot"
            class="cursor-pointer rounded-lg shadow-sm border border-gray-200">
        <button type="button" id="remove-chart-btn"
            class="btn btn-circle btn-sm btn-error absolute -top-2 -right-2 shadow-md">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
            </svg>
        </button>
    </div>
@endif

<div id="new-chart-preview" class="relative mt-4 mb-4" style="display: none;">
    <img id="new-chart-img" src="" alt="Chart Preview" class="rounded-lg shadow-sm border border-gray-200">
    <button type="button" id="remove-chart-preview-btn"
        class="btn btn-circle btn-sm btn-error absolute -top-2 -right-2 shadow-md" id="remove-chart-btn">
        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
        </svg>
    </button>
</div>

<input type="hidden" name="remove_chart_picture" id="remove-chart-input" value="0">
<input type="file" id="chart_screenshot" name="chart_screenshot"
    class="file-input file-input-primary w-full max-w-xs block mt-2" accept="image/*" />