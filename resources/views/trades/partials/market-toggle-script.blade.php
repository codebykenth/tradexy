<!-- Market Toggle Script (MyTrade fee auto-calc for PSE) -->
<script>
    (function () {
        const marketInput = document.getElementById('market-input');
        const tabs = document.querySelectorAll('.market-tab');
        const cryptoFields = document.querySelectorAll('.crypto-only-field');
        const pseFields = document.querySelectorAll('.pse-only-field');
        const cryptoTimeframes = document.querySelectorAll('.crypto-timeframe');
        const pseTimeframes = document.querySelectorAll('.pse-timeframe');
        const symbolInput = document.getElementById('symbol-input');
        const timeframeSelect = document.getElementById('timeframe-select');

        // MyTrade (Abacus Securities) fixed rate constants
        const MYTRADE_COMMISSION_RATE = 0.0025;   // 0.25% on buy & sell
        const MYTRADE_VAT_RATE = 0.12;            // 12% VAT on commission
        const MYTRADE_PSE_TRANS_RATE = 0.00005;   // 0.005% on buy & sell
        const MYTRADE_SCCP_RATE = 0.0001;         // 0.01% on buy & sell
        const MYTRADE_SALES_TAX_RATE = 0.001;     // 0.1% on sell only (since Jul 2025)

        function setMarket(market) {
            marketInput.value = market;

            // Toggle active tab styling
            tabs.forEach(tab => {
                if (tab.dataset.market === market) {
                    tab.classList.add('bg-primary', 'text-white', 'shadow-sm');
                    tab.classList.remove('text-gray-600', 'hover:bg-gray-300');
                } else {
                    tab.classList.remove('bg-primary', 'text-white', 'shadow-sm');
                    tab.classList.add('text-gray-600', 'hover:bg-gray-300');
                }
            });

            const isCrypto = market === 'crypto';

            // Toggle field visibility
            cryptoFields.forEach(el => el.style.display = isCrypto ? '' : 'none');
            pseFields.forEach(el => el.style.display = isCrypto ? 'none' : '');

            // Toggle timeframe options
            cryptoTimeframes.forEach(opt => opt.style.display = isCrypto ? '' : 'none');
            pseTimeframes.forEach(opt => opt.style.display = isCrypto ? 'none' : '');

            // Update placeholder
            if (symbolInput) {
                symbolInput.placeholder = isCrypto ? 'BTCUSDT' : 'JFC';
            }

            // Reset timeframe if currently selected option is hidden
            if (timeframeSelect) {
                const selected = timeframeSelect.options[timeframeSelect.selectedIndex];
                if (selected && selected.style.display === 'none') {
                    timeframeSelect.selectedIndex = 0;
                }
            }

            // For PSE: set entry side to long for PnL calculation
            const entrySideEl = document.getElementById('entry-side');
            if (!isCrypto && entrySideEl) {
                entrySideEl.value = 'long';
            }

            // Recalculate PSE fees when switching to PSE
            if (!isCrypto) calculatePseFees();
        }

        // Auto-calculate MyTrade PSE fees from entry/exit values
        function calculatePseFees() {
            if (marketInput.value !== 'pse') return;

            const qty = parseFloat(document.querySelector('.quantity')?.value) || 0;
            const entryPrice = parseFloat(document.getElementById('avg-entry-price')?.value) || 0;
            const exitPrice = parseFloat(document.getElementById('avg-exit-price')?.value) || 0;

            const buyValue = qty * entryPrice;
            const sellValue = qty * exitPrice;
            const totalGross = buyValue + sellValue;

            // Broker commission: 0.25% on both buy and sell
            const brokerComm = totalGross * MYTRADE_COMMISSION_RATE;

            // VAT: 12% of broker commission
            const vat = brokerComm * MYTRADE_VAT_RATE;

            // PSE Trans Fee: 0.005% on both buy and sell
            const pseTrans = totalGross * MYTRADE_PSE_TRANS_RATE;

            // SCCP Fee: 0.01% on both buy and sell
            const sccp = totalGross * MYTRADE_SCCP_RATE;

            // Sales Tax: 0.1% on sell only
            const salesTax = sellValue * MYTRADE_SALES_TAX_RATE;

            // Update form fields
            const fields = {
                'broker_commission': brokerComm,
                'pse_vat': vat,
                'pse_trans_fee': pseTrans,
                'sccp_fee': sccp,
                'sales_tax': salesTax,
            };

            let totalFees = 0;
            for (const [name, value] of Object.entries(fields)) {
                const el = document.querySelector(`input[name="${name}"]`);
                if (el) el.value = value > 0 ? value.toFixed(4) : '';
                totalFees += value;
            }

            // Sync to open_fees/close_fees for PnL calculation
            const openFeesEl = document.getElementById('open-fees');
            const closeFeesEl = document.getElementById('close-fees');
            if (openFeesEl) openFeesEl.value = (totalFees / 2).toFixed(8);
            if (closeFeesEl) closeFeesEl.value = (totalFees - totalFees / 2).toFixed(8);

            // Trigger PnL recalculation
            if (typeof calculateAll === 'function') calculateAll();
        }

        // Attach click handlers
        tabs.forEach(tab => {
            tab.addEventListener('click', () => setMarket(tab.dataset.market));
        });

        // Recalculate PSE fees when price/qty changes
        const triggerInputs = [
            document.querySelector('.quantity'),
            document.getElementById('avg-entry-price'),
            document.getElementById('avg-exit-price'),
        ];
        triggerInputs.forEach(input => {
            if (input) {
                input.addEventListener('input', calculatePseFees);
                input.addEventListener('change', calculatePseFees);
            }
        });

        // Allow manual PSE fee override — recalculate PnL totals when edited
        document.querySelectorAll('.pse-fee-input').forEach(input => {
            input.addEventListener('input', () => {
                if (marketInput.value !== 'pse') return;

                let totalFees = 0;
                document.querySelectorAll('.pse-fee-input').forEach(i => totalFees += parseFloat(i.value) || 0);

                const openFeesEl = document.getElementById('open-fees');
                const closeFeesEl = document.getElementById('close-fees');
                if (openFeesEl) openFeesEl.value = (totalFees / 2).toFixed(8);
                if (closeFeesEl) closeFeesEl.value = (totalFees - totalFees / 2).toFixed(8);

                if (typeof calculateAll === 'function') calculateAll();
            });
        });

        // Initialize on page load
        setMarket(marketInput.value || 'crypto');
    })();
</script>