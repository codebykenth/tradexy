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

            // Trigger main calculation logic
            if (typeof calculateAll === 'function') {
                calculateAll();
            }
        }

        // Attach click handlers
        tabs.forEach(tab => {
            tab.addEventListener('click', () => setMarket(tab.dataset.market));
        });

        // Initialize on page load
        setMarket(marketInput.value || 'crypto');
    })();
</script>