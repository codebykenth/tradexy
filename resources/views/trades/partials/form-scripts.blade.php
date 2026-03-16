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

    // PSE Fee Elements
    let brokerCommissionEl = document.getElementById('pse-broker-commission')
    let pseTransFeeEl = document.getElementById('pse-trans-fee')
    let sccpFeeEl = document.getElementById('pse-sccp-fee')
    let pseVatEl = document.getElementById('pse-vat')
    let salesTaxEl = document.getElementById('pse-sales-tax')

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

        // Market Detection
        let marketInput = document.getElementById('market-input');
        let currentMarket = marketInput ? marketInput.value : 'crypto';

        // Calculate PSE Fees (MyTrade Rates)
        if (currentMarket === 'pse' && qty > 0 && entryPrice > 0) {
            let buyValue = qty * entryPrice;
            let sellValue = qty * exitPrice;

            // Commission (0.25% of gross value)
            let buyComm = buyValue * 0.0025;
            let sellComm = sellValue > 0 ? sellValue * 0.0025 : 0;
            let totalComm = buyComm + sellComm;

            // VAT (12% of commission)
            let buyVat = buyComm * 0.12;
            let sellVat = sellComm * 0.12;
            let totalVat = buyVat + sellVat;

            // SCCP Fee (0.01% of gross transaction value)
            let buySccp = buyValue * 0.0001;
            let sellSccp = sellValue * 0.0001;
            let totalSccp = buySccp + sellSccp;

            // PSE Trans Fee (0.005% of gross transaction value)
            let buyPseTrans = buyValue * 0.00005;
            let sellPseTrans = sellValue * 0.00005;
            let totalPseTrans = buyPseTrans + sellPseTrans;

            // Sales Tax (0.10% of gross transaction value, sellers only)
            let totalSalesTax = sellValue * 0.001;

            if (brokerCommissionEl) brokerCommissionEl.value = totalComm > 0 ? totalComm.toFixed(2) : '';
            if (pseVatEl) pseVatEl.value = totalVat > 0 ? totalVat.toFixed(2) : '';
            if (sccpFeeEl) sccpFeeEl.value = totalSccp > 0 ? totalSccp.toFixed(2) : '';
            if (pseTransFeeEl) pseTransFeeEl.value = totalPseTrans > 0 ? totalPseTrans.toFixed(2) : '';
            if (salesTaxEl) salesTaxEl.value = totalSalesTax > 0 ? totalSalesTax.toFixed(2) : '';
        }

        // Calculate Gross PNL — PSE is always long
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
            if (grossPnlEl) grossPnlEl.value = grossPnl.toFixed(2);
        } else {
            if (grossPnlEl) grossPnlEl.value = '';
        }

        // Calculate Net PNL (Gross Pnl - Fees)
        let totalFees = 0;
        if (currentMarket === 'pse') {
            totalFees = (parseFloat(brokerCommissionEl ? brokerCommissionEl.value : 0) || 0) +
                        (parseFloat(pseTransFeeEl ? pseTransFeeEl.value : 0) || 0) +
                        (parseFloat(sccpFeeEl ? sccpFeeEl.value : 0) || 0) +
                        (parseFloat(pseVatEl ? pseVatEl.value : 0) || 0) +
                        (parseFloat(salesTaxEl ? salesTaxEl.value : 0) || 0);
        } else {
            let openFees = openFeesEl ? (parseFloat(openFeesEl.value) || 0) : 0;
            let closeFees = closeFeesEl ? (parseFloat(closeFeesEl.value) || 0) : 0;
            totalFees = openFees + closeFees;
        }

        if (grossPnlEl && grossPnlEl.value !== '') {
            let netPnl = grossPnl - totalFees;
            if (netPnlEl) netPnlEl.value = netPnl.toFixed(2);
        } else {
            if (netPnlEl) netPnlEl.value = '';
        }
    }

    // Attach listener to ALL inputs that completely change the math
    let inputsToListen = [
        quantityEl, avgEntryPriceEl, avgExitPriceEl, 
        openFeesEl, closeFeesEl, entrySideEl, exitSideEl,
        brokerCommissionEl, pseTransFeeEl, sccpFeeEl, pseVatEl, salesTaxEl
    ];

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
        const input = document.getElementById('remove-chart-input');
        input.value = '1';
        input.dispatchEvent(new Event('change', { bubbles: true }));
        document.getElementById('chart-preview-container').style.display = 'none';
    })

    removeChartPreviewBtnEl.addEventListener('click', function () {
        document.getElementById('new-chart-preview').style.display = 'none';
        document.getElementById('new-chart-img').src = '';
        
        const fileInput = document.getElementById('chart_screenshot');
        if (fileInput) fileInput.value = '';
        
        const removeInput = document.getElementById('remove-chart-input');
        removeInput.value = '0';
        removeInput.dispatchEvent(new Event('change', { bubbles: true }));

        let oldContainer = document.getElementById('chart-preview-container');
        if (oldContainer) oldContainer.style.display = 'inline-block';
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

            const removeInput = document.getElementById('remove-chart-input');
            removeInput.value = '1';
            removeInput.dispatchEvent(new Event('change', { bubbles: true }));
        }
    })

</script>

<style>
    /* Automatically hide the delete button in the FIRST reason-container of any fieldset */
    .reasons-fieldset .reason-container:first-of-type .delete-btn {
        display: none !important;
    }
</style>