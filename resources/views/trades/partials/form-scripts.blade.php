<!-- Add reasons and lessons -->
<script>
    let addReasonBtn = document.querySelectorAll('.add-reason-btn')
    let reasonsFieldsetEl = document.querySelectorAll('.reasons-fieldset')
    let deleteBtn = document.querySelectorAll('.delete-btn')

    let newHtmlString = `
            <div class="flex items-center gap-2 w-full reason-container">
                <input type="text" placeholder="Add reason" class="input flex-grow" name="reason[]" />
                <button type="button" class="btn btn-square btn-error btn-outline delete-btn"
                    aria-label="Delete Reason">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                        stroke-width="1.5" stroke="currentColor" class="size-5">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="m14.74 9-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 0 1-2.244 2.077H8.084a2.25 2.25 0 0 1-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 0 0-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 0 1 3.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 0 0-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 0 0-7.5 0" />
                    </svg>
                </button>
            </div>
        `

    // Add reason for entry
    for (let i = 0; i < addReasonBtn.length; i++) {
        addReasonBtn[i].addEventListener('click', function () {
            addReasonBtn[i].insertAdjacentHTML('beforebegin', newHtmlString)
        })
    }

    // Remove added reason for entry
    for (let i = 0; i < reasonsFieldsetEl.length; i++) {
        reasonsFieldsetEl[i].addEventListener('click', function (event) {
            let reasonContainerCount = reasonsFieldsetEl[i].querySelectorAll('.reason-container').length
            let clickedDeleteButton = event.target.closest('.delete-btn')
            if (clickedDeleteButton && reasonContainerCount > 1) {
                clickedDeleteButton.closest('.reason-container').remove()
            }
        })
    }
</script>

<!-- Realtime Values -->
<script>
    let quantityEl = document.querySelector('.quantity')
    let avgEntryPriceEl = document.getElementById('avg-entry-price')
    let totalEntryValEl = document.getElementById('total-entry-val')
    let closedSizeEl = document.getElementById('closed-size')
    let totalExitValEl = document.getElementById('total-exit-val')
    let avgExitPriceEl = document.getElementById('avg-exit-price')
    let entrySideEl = document.getElementById('entry-side')
    let exitSideEl = document.getElementById('exit-side')

    // Pnl Elements
    let openFeesEl = document.getElementById('open-fees')
    let closeFeesEl = document.getElementById('close-fees')
    let grossPnlEl = document.getElementById('gross-pnl')
    let netPnlEl = document.getElementById('total-pnl')

    // Sync Entry/Exit sides automatically
    if (entrySideEl && exitSideEl) {
        const toggleSide = (val) => val === 'long' ? 'short' : 'long';
        entrySideEl.addEventListener('change', () => exitSideEl.value = toggleSide(entrySideEl.value));
        exitSideEl.addEventListener('change', () => entrySideEl.value = toggleSide(exitSideEl.value));
    }

    function calculateAll() {
        if (!quantityEl || !avgEntryPriceEl) return;

        // Calculate Entry/Exit Values
        let qty = parseFloat(quantityEl.value) || 0;
        let entryPrice = parseFloat(avgEntryPriceEl.value) || 0;
        let exitPrice = avgExitPriceEl ? (parseFloat(avgExitPriceEl.value) || 0) : 0;

        let totalEntry = qty * entryPrice;
        let totalExit = qty * exitPrice;

        if (totalEntryValEl) totalEntryValEl.value = totalEntry > 0 ? totalEntry : '';
        if (closedSizeEl) closedSizeEl.value = qty > 0 ? qty : '';
        if (totalExitValEl) totalExitValEl.value = totalExit > 0 ? totalExit : '';

        // Calculate Gross PNL
        let entrySide = entrySideEl && entrySideEl.value ? entrySideEl.value.toLowerCase() : '';
        let exitSide = exitSideEl && exitSideEl.value ? exitSideEl.value.toLowerCase() : '';

        let grossPnl = 0;

        if (entrySide === 'long') {
            grossPnl = (exitPrice - entryPrice) * qty;
        } else if (entrySide === 'short') {
            grossPnl = (entryPrice - exitPrice) * qty;
        }

        // Only display if there is entry/exit/qty
        if (entryPrice > 0 && exitPrice > 0 && qty > 0) {
            if (grossPnlEl) grossPnlEl.value = grossPnl;
        } else {
            if (grossPnlEl) grossPnlEl.value = '';
        }

        // Calculate Net PNL (Gross Pnl - Fees)
        let openFees = openFeesEl ? (parseFloat(openFeesEl.value) || 0) : 0;
        let closeFees = closeFeesEl ? (parseFloat(closeFeesEl.value) || 0) : 0;

        if (grossPnlEl && grossPnlEl.value !== '') {
            let netPnl = grossPnl - openFees - closeFees;
            if (netPnlEl) netPnlEl.value = netPnl;
        } else {
            if (netPnlEl) netPnlEl.value = '';
        }
    }

    // Attach listener to ALL inputs that completely change the math
    let inputsToListen = [quantityEl, avgEntryPriceEl, avgExitPriceEl, openFeesEl, closeFeesEl, entrySideEl, exitSideEl];

    inputsToListen.forEach(input => {
        if (input) {
            // Listen for input, or 'change' for the select dropdown
            input.addEventListener('input', calculateAll);
            input.addEventListener('change', calculateAll);
        }
    });

    calculateAll();
</script>

<!-- Chart Screenshot -->
<script>
    const removeChartBtnEl = document.getElementById('remove-chart-btn')
    const removeChartPreviewBtnEl = document.getElementById('remove-chart-preview-btn')
    const chartScreenshotEl = document.getElementById('chart_screenshot')

    removeChartBtnEl.addEventListener('click', function () {
        document.getElementById('remove-chart-input').value = '1';
        document.getElementById('chart-preview-container').style.display = 'none';
    })

    removeChartPreviewBtnEl.addEventListener('click', function () {
        document.getElementById('new-chart-preview').style.display = 'none';
        document.getElementById('new-chart-img').src = '';
        document.querySelector('input[name=\'chart_screenshot\']').value = '';
        let oldContainer = document.getElementById('chart-preview-container');
        if (oldContainer) oldContainer.style.display = 'inline-block';
        document.getElementById('remove-chart-input').value = '0';
    })

    chartScreenshotEl.addEventListener('change', function () {
        if (this.files && this.files[0]) {
            let reader = new FileReader();
            reader.onload = function (e) {
                document.getElementById('new-chart-img').src = e.target.result;
            };
            reader.readAsDataURL(this.files[0]);

            document.getElementById('new-chart-preview').style.display = 'inline-block';

            // Hide old image if it exists
            let oldContainer = document.getElementById('chart-preview-container');
            if (oldContainer) oldContainer.style.display = 'none';

            document.getElementById('remove-chart-input').value = '1';
        }
    })

</script>

<style>
    /* Automatically hide the delete button in the FIRST reason-container of any fieldset */
    .reasons-fieldset .reason-container:first-of-type .delete-btn {
        display: none !important;
    }
</style>