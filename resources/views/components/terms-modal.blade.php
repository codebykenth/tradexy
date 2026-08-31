@auth
    @if (is_null(auth()->user()->terms_accepted_at))
        <dialog id="terms_acceptance_modal" class="modal modal-open z-50 pointer-events-auto" onkeydown="if(event.key==='Escape') event.preventDefault();">
            <div class="modal-box max-w-lg border border-base-300 shadow-2xl rounded-2xl p-6 sm:p-8 bg-base-100 text-base-content relative">
                <!-- Header Icon & Title -->
                <div class="flex items-center gap-3.5 mb-5">
                    <div class="p-3 bg-primary/10 text-primary rounded-xl flex items-center justify-center shrink-0">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75m-3-7.036A11.959 11.959 0 0 1 3.598 6 11.99 11.99 0 0 0 3 9.749c0 5.592 3.824 10.29 9 11.623 5.176-1.332 9-6.03 9-11.622 0-1.31-.21-2.571-.598-3.751h-.152c-3.196 0-6.1-1.248-8.25-3.285Z" />
                        </svg>
                    </div>
                    <div>
                        <h3 class="font-bold text-lg sm:text-xl tracking-tight text-base-content">Terms & Agreement</h3>
                        <p class="text-xs text-base-content/60">Required to continue to your journal</p>
                    </div>
                </div>

                <p class="text-sm text-base-content/80 mb-4 leading-relaxed">
                    Welcome to Tradexy! Before continuing, please review and accept our platform terms, financial disclaimer, and privacy policy.
                </p>

                <!-- Scrollable Summary -->
                <div class="max-h-60 overflow-y-auto space-y-3.5 p-4 bg-base-200/60 rounded-xl border border-base-300 text-xs sm:text-sm text-base-content/80 leading-relaxed mb-4 select-text">
                    <div>
                        <h4 class="font-semibold text-base-content mb-1">1. Documentation & Analytical Tool Only</h4>
                        <p class="text-base-content/70">Tradexy is strictly a documentation, backtesting, and analytics tool. We do not provide financial, investment, or trading advice.</p>
                    </div>
                    <div>
                        <h4 class="font-semibold text-base-content mb-1">2. No Liability for Trading Losses</h4>
                        <p class="text-base-content/70">Trading in financial markets involves substantial risk of loss. You retain full responsibility for your trading decisions and executions.</p>
                    </div>
                    <div>
                        <h4 class="font-semibold text-base-content mb-1">3. Data Privacy & Ownership</h4>
                        <p class="text-base-content/70">Your trades, strategies, notes, and journal entries are your intellectual property. We do not sell your personal data or trade secrets.</p>
                    </div>
                    <div>
                        <h4 class="font-semibold text-base-content mb-1">4. AI-Generated Content Disclaimer</h4>
                        <p class="text-base-content/70">Automated news summaries and AI trade audits are educational insights and may contain inaccuracies. Never rely solely on AI for time-sensitive executions.</p>
                    </div>
                </div>

                <div class="flex items-center justify-between text-xs text-base-content/70 mb-6 px-1">
                    <span>Read complete documents:</span>
                    <div class="flex gap-3">
                        <a href="{{ route('terms') }}" target="_blank" rel="noopener noreferrer" class="link link-primary font-medium">Terms of Service</a>
                        <a href="{{ route('privacy') }}" target="_blank" rel="noopener noreferrer" class="link link-primary font-medium">Privacy Policy</a>
                    </div>
                </div>

                <!-- Accept Form -->
                <form action="{{ route('terms.accept') }}" method="POST" class="space-y-2">
                    @csrf
                    <button type="submit" class="btn btn-primary w-full shadow-md hover:shadow-lg transition-all">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5" />
                        </svg>
                        I Accept the Terms & Privacy Policy
                    </button>
                </form>

                <!-- Decline & Logout -->
                <form action="{{ route('logout') }}" method="POST" class="mt-2 text-center" data-turbo="false">
                    @csrf
                    <button type="submit" class="btn btn-ghost btn-xs text-base-content/50 hover:text-error w-full">
                        Decline and Log Out
                    </button>
                </form>
            </div>
        </dialog>

        <script>
            (function() {
                const initTermsModal = () => {
                    const modal = document.getElementById('terms_acceptance_modal');
                    if (modal) {
                        if (typeof modal.showModal === 'function' && !modal.open) {
                            try {
                                modal.showModal();
                            } catch (e) {
                                // fallback if already open
                            }
                        }
                        modal.addEventListener('cancel', (e) => e.preventDefault());
                    }
                };

                if (document.readyState === 'loading') {
                    document.addEventListener('DOMContentLoaded', initTermsModal);
                } else {
                    initTermsModal();
                }
                document.addEventListener('turbo:load', initTermsModal);
            })();
        </script>
    @endif
@endauth
