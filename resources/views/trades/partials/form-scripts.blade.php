@include('components.dynamic-repeater-script')

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

        // Calculate Gross PNL — PSE is always long
        let marketInput = document.getElementById('market-input');
        let currentMarket = marketInput ? marketInput.value : 'crypto';
        let entrySide = currentMarket === 'pse' ? 'long' : (entrySideEl && entrySideEl.value ? entrySideEl.value.toLowerCase() : '');
        let exitSide = currentMarket === 'pse' ? 'short' : (exitSideEl && exitSideEl.value ? exitSideEl.value.toLowerCase() : '');

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