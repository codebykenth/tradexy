<x-layouts.app title="Tradexy">

    <main class="w-full">
        <!-- Hero -->
        <section class="max-w-7xl mx-auto px-6 py-12 lg:py-24 grid lg:grid-cols-2 gap-12 lg:gap-24 items-center">
            <div class="flex flex-col justify-center space-y-8">
                <div
                    class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-blue-50 dark:bg-blue-900/20 text-blue-600 dark:text-blue-300 text-xs font-medium w-fit border border-blue-100 dark:border-blue-900/30">
                    <span class="relative flex h-2 w-2">
                        <span
                            class="animate-ping absolute inline-flex h-full w-full rounded-full bg-blue-400 opacity-75"></span>
                        <span class="relative inline-flex rounded-full h-2 w-2 bg-blue-500"></span>
                    </span>
                    New: AI-Powered Market Analysis
                </div>

                <div class="space-y-6">
                    <h1
                        class="text-4xl lg:text-7xl font-bold tracking-tight text-gray-900 dark:text-white leading-[1.1] text-balance">
                        The Best <span class="text-primary">Crypto & PSE (PH Market)</span> Trading Journal. <br>
                        <span class="text-gray-400 dark:text-gray-600">Scale Your Edge.</span>
                    </h1>
                    <p class="text-lg text-gray-600 dark:text-gray-400 leading-relaxed max-w-lg text-balance">
                        The professional trading journal that helps you find your edge. Log your trades, backtest
                        strategies,
                        and use AI insights to become a consistent, profitable trader.
                    </p>
                </div>

                <div class="pt-2">
                    <a href="{{ route('register') }}"
                        class="inline-flex items-center justify-center px-8 py-3 text-sm font-semibold text-white transition-all bg-gray-900 rounded-lg hover:bg-gray-800 dark:bg-white dark:text-gray-900 dark:hover:bg-gray-200 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-900 dark:focus:ring-white">
                        Start Journaling Free
                    </a>
                </div>

                <div
                    class="flex flex-col sm:flex-row gap-6 sm:gap-12 py-8 border-t border-gray-100 dark:border-gray-800">
                    <div class="flex items-center gap-3">
                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none"
                            stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                            class="text-gray-400 dark:text-gray-500">
                            <path
                                d="M20 13c0 5-3.5 7.5-7.66 8.95a1 1 0 0 1-.67-.01C7.5 20.5 4 18 4 13V6a1 1 0 0 1 1-1c2 0 4.5-1.2 6.24-2.72a1.17 1.17 0 0 1 1.52 0C14.51 3.81 17 5 19 5a1 1 0 0 1 1 1z" />
                            <path d="m9 12 2 2 4-4" />
                        </svg>
                        <p class="text-sm font-medium text-gray-700 dark:text-gray-300">100% Private Data</p>
                    </div>
                    <div class="flex items-center gap-3">
                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none"
                            stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                            class="text-gray-400 dark:text-gray-500">
                            <path
                                d="m12 3-1.912 5.813a2 2 0 0 1-1.275 1.275L3 12l5.813 1.912a2 2 0 0 1 1.275 1.275L12 21l1.912-5.813a2 2 0 0 1 1.275-1.275L21 12l-5.813-1.912a2 2 0 0 1-1.275-1.275Z" />
                        </svg>
                        <p class="text-sm font-medium text-gray-700 dark:text-gray-300">AI Insights</p>
                    </div>
                    <div class="flex items-center gap-3">
                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none"
                            stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                            class="text-gray-400 dark:text-gray-500">
                            <polyline points="22 7 13.5 15.5 8.5 10.5 2 17" />
                            <polyline points="16 7 22 7 22 13" />
                        </svg>
                        <p class="text-sm font-medium text-gray-700 dark:text-gray-300">Advanced Backtesting</p>
                    </div>
                </div>
            </div>

            <div class="relative group">
                <div
                    class="absolute -inset-1 bg-gradient-to-r from-blue-600 to-indigo-600 rounded-2xl blur opacity-25 group-hover:opacity-40 transition duration-1000 group-hover:duration-200">
                </div>
                <img src="{{ asset('images/tradexy-hero-1.png') }}" alt="Hero Image"
                    class="relative w-full h-auto rounded-2xl shadow-2xl border border-gray-200 dark:border-gray-800 bg-gray-50 dark:bg-gray-900 object-cover aspect-[4/3]">
            </div>
        </section>

        <!-- Features -->
        <section id="features" class="py-24 bg-gray-50 dark:bg-[#0F0F0E] border-y border-gray-200 dark:border-[#1F1F1E]">
            <div class="max-w-7xl mx-auto px-6">
                <div class="max-w-3xl mx-auto text-center space-y-4 mb-20">
                    <div class="text-blue-600 dark:text-blue-400 font-semibold tracking-wide uppercase text-sm">Powerful
                        Features</div>
                    <h2 class="text-3xl md:text-4xl font-bold text-gray-900 dark:text-white tracking-tight">Everything
                        You Need to Scale</h2>
                    <p class="text-lg text-gray-600 dark:text-gray-400 leading-relaxed">
                        Tradexy isn't just a logbook. It's a comprehensive analytics suite designed to highlight your
                        strengths
                        and fix your weaknesses.
                    </p>
                </div>

                <!-- Feature Cards Grid -->
                <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-8">
                    <!-- Deep Performance Analytics -->
                    <div
                        class="p-8 bg-white dark:bg-[#161615] border border-gray-200 dark:border-[#272726] rounded-2xl hover:border-blue-500/30 dark:hover:border-blue-500/30 transition-colors group">
                        <div
                            class="w-12 h-12 bg-blue-50 dark:bg-blue-900/10 rounded-xl flex items-center justify-center text-blue-600 dark:text-blue-400 mb-6 group-hover:scale-110 transition-transform duration-300">
                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"
                                fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                stroke-linejoin="round">
                                <line x1="18" x2="18" y1="20" y2="10" />
                                <line x1="12" x2="12" y1="20" y2="4" />
                                <line x1="6" x2="6" y1="20" y2="14" />
                            </svg>
                        </div>
                        <h3 class="text-xl font-semibold mb-3 text-gray-900 dark:text-white">Deep Performance Analytics
                        </h3>
                        <p class="text-gray-600 dark:text-gray-400 leading-relaxed text-sm">Visualize your equity curve,
                            analyze win/loss ratios by timeframe, and identify which assets generate your highest
                            returns.</p>
                    </div>

                    <!-- Effortless Trade Logging -->
                    <div
                        class="p-8 bg-white dark:bg-[#161615] border border-gray-200 dark:border-[#272726] rounded-2xl hover:border-orange-500/30 dark:hover:border-orange-500/30 transition-colors group">
                        <div
                            class="w-12 h-12 bg-orange-50 dark:bg-orange-900/10 rounded-xl flex items-center justify-center text-orange-600 dark:text-orange-400 mb-6 group-hover:scale-110 transition-transform duration-300">
                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"
                                fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                stroke-linejoin="round">
                                <polygon points="13 2 3 14 12 14 11 22 21 10 12 10 13 2" />
                            </svg>
                        </div>
                        <h3 class="text-xl font-semibold mb-3 text-gray-900 dark:text-white">Effortless Trade Logging
                        </h3>
                        <p class="text-gray-600 dark:text-gray-400 leading-relaxed text-sm">Import trades via CSV or use
                            our blazing fast manual entry. Add screenshots, notes, and tags to never forget a setup.</p>
                    </div>

                    <!-- Strategy Backtesting Engine -->
                    <div
                        class="p-8 bg-white dark:bg-[#161615] border border-gray-200 dark:border-[#272726] rounded-2xl hover:border-green-500/30 dark:hover:border-green-500/30 transition-colors group">
                        <div
                            class="w-12 h-12 bg-green-50 dark:bg-green-900/10 rounded-xl flex items-center justify-center text-green-600 dark:text-green-400 mb-6 group-hover:scale-110 transition-transform duration-300">
                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"
                                fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                stroke-linejoin="round">
                                <path
                                    d="M10 2v7.527a2 2 0 0 1-.211.896L4.72 20.55a1 1 0 0 0 .9 1.45h12.76a1 1 0 0 0 .9-1.45l-5.069-10.127A2 2 0 0 1 14 9.527V2" />
                                <path d="M8.5 2h7" />
                                <path d="M7 16h10" />
                            </svg>
                        </div>
                        <h3 class="text-xl font-semibold mb-3 text-gray-900 dark:text-white">Strategy Backtesting Engine
                        </h3>
                        <p class="text-gray-600 dark:text-gray-400 leading-relaxed text-sm">Don't risk real capital on
                            unproven ideas. Define your strategy, run backtests, and validate your edge with statistical
                            confidence.</p>
                    </div>

                    <!-- AI Market Intelligence -->
                    <div
                        class="p-8 bg-white dark:bg-[#161615] border border-gray-200 dark:border-[#272726] rounded-2xl hover:border-purple-500/30 dark:hover:border-purple-500/30 transition-colors group">
                        <div
                            class="w-12 h-12 bg-purple-50 dark:bg-purple-900/10 rounded-xl flex items-center justify-center text-purple-600 dark:text-purple-400 mb-6 group-hover:scale-110 transition-transform duration-300">
                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"
                                fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                stroke-linejoin="round">
                                <path d="M12 8V4H8" />
                                <rect width="16" height="12" x="4" y="8" rx="2" />
                                <path d="M2 14h2" />
                                <path d="M20 14h2" />
                                <path d="M15 13v2" />
                                <path d="M9 13v2" />
                            </svg>
                        </div>
                        <h3 class="text-xl font-semibold mb-3 text-gray-900 dark:text-white">AI Market Intelligence</h3>
                        <p class="text-gray-600 dark:text-gray-400 leading-relaxed text-sm">Stay ahead of the narrative.
                            Our Gemini AI integration curates and summarizes critical market news and sentiment in
                            seconds.</p>
                    </div>

                    <!-- Portfolio Growth Tracking -->
                    <div
                        class="p-8 bg-white dark:bg-[#161615] border border-gray-200 dark:border-[#272726] rounded-2xl hover:border-teal-500/30 dark:hover:border-teal-500/30 transition-colors group">
                        <div
                            class="w-12 h-12 bg-teal-50 dark:bg-teal-900/10 rounded-xl flex items-center justify-center text-teal-600 dark:text-teal-400 mb-6 group-hover:scale-110 transition-transform duration-300">
                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"
                                fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                stroke-linejoin="round">
                                <path d="M19 7V4a1 1 0 0 0-1-1H5a2 2 0 0 0 0 4v12a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-3" />
                                <path
                                    d="M16 10.5V10a2 2 0 0 0-2-2h-3.83a2 2 0 0 0-1.22.42l-4.11 3.29A2 2 0 0 0 4 13.26V13a2 2 0 0 0 2 2h4.5a2 2 0 0 0 2-2v-.65a2 2 0 0 1 2-2h1.5a2 2 0 0 1 2 2v2.5a2 2 0 0 1-2 2H16" />
                            </svg>
                        </div>
                        <h3 class="text-xl font-semibold mb-3 text-gray-900 dark:text-white">Portfolio Growth Tracking
                        </h3>
                        <p class="text-gray-600 dark:text-gray-400 leading-relaxed text-sm">Aggregate your wallet
                            balances across exchanges. Track your net worth growth and daily percentage changes clearly.
                        </p>
                    </div>

                    <!-- Strategy Management -->
                    <div
                        class="p-8 bg-white dark:bg-[#161615] border border-gray-200 dark:border-[#272726] rounded-2xl hover:border-indigo-500/30 dark:hover:border-indigo-500/30 transition-colors group">
                        <div
                            class="w-12 h-12 bg-indigo-50 dark:bg-indigo-900/10 rounded-xl flex items-center justify-center text-indigo-600 dark:text-indigo-400 mb-6 group-hover:scale-110 transition-transform duration-300">
                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"
                                fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                stroke-linejoin="round">
                                <path
                                    d="m12.83 2.18a2 2 0 0 0-1.66 0L2.6 6.08a1 1 0 0 0 0 1.83l8.58 3.91a2 2 0 0 0 1.66 0l8.58-3.9a1 1 0 0 0 0-1.83Z" />
                                <path d="m22 17.65-9.17 4.16a2 2 0 0 1-1.66 0L2 17.65" />
                                <path d="m22 12.65-9.17 4.16a2 2 0 0 1-1.66 0L2 12.65" />
                            </svg>
                        </div>
                        <h3 class="text-xl font-semibold mb-3 text-gray-900 dark:text-white">Strategy Management</h3>
                        <p class="text-gray-600 dark:text-gray-400 leading-relaxed text-sm">Organize your trading
                            systems. Tag entries by strategy to see which setups pay the bills and which ones drain your
                            account.</p>
                    </div>
                </div>
            </div>
        </section>

        <!-- How it Works (Loop) -->
        <section id="how-it-works" class="py-24 max-w-7xl mx-auto px-6">
            <div class="text-center max-w-2xl mx-auto mb-20 space-y-4">
                <p class="text-sm font-semibold uppercase tracking-widest text-gray-500 dark:text-gray-400">Workflow</p>
                <h2 class="text-3xl md:text-4xl font-bold text-gray-900 dark:text-white tracking-tight">The Loop of
                    Profitability</h2>
                <p class="text-lg text-gray-600 dark:text-gray-400">A simple process to refine your psychological and
                    technical edge.</p>
            </div>

            <div class="grid md:grid-cols-3 gap-12 relative">
                <!-- Connector Line (Desktop) -->
                <div
                    class="hidden md:block absolute top-[24px] left-0 w-full h-px bg-gradient-to-r from-transparent via-gray-200 dark:via-gray-800 to-transparent z-0">
                </div>

                <!-- Step 1 -->
                <div class="relative flex flex-col items-center text-center space-y-4 z-10">
                    <div
                        class="w-12 h-12 rounded-full bg-white dark:bg-[#0a0a0a] border-2 border-gray-900 dark:border-white text-gray-900 dark:text-white flex items-center justify-center font-bold text-lg shadow-sm">
                        1</div>
                    <h3 class="text-xl font-semibold text-gray-900 dark:text-white">Log Trades</h3>
                    <p class="text-gray-600 dark:text-gray-400 text-sm leading-relaxed max-w-xs">Record every trade
                        details. The more data you feed, the sharper your insights become.</p>
                </div>

                <!-- Step 2 -->
                <div class="relative flex flex-col items-center text-center space-y-4 z-10">
                    <div
                        class="w-12 h-12 rounded-full bg-white dark:bg-[#0a0a0a] border-2 border-gray-200 dark:border-gray-800 text-gray-500 dark:text-gray-400 flex items-center justify-center font-bold text-lg shadow-sm">
                        2</div>
                    <h3 class="text-xl font-semibold text-gray-900 dark:text-white">Analyze & Refine</h3>
                    <p class="text-gray-600 dark:text-gray-400 text-sm leading-relaxed max-w-xs">Review your dashboard.
                        Identify patterns in your winners and losers. Eliminate what doesn't work.</p>
                </div>

                <!-- Step 3 -->
                <div class="relative flex flex-col items-center text-center space-y-4 z-10">
                    <div
                        class="w-12 h-12 rounded-full bg-white dark:bg-[#0a0a0a] border-2 border-gray-200 dark:border-gray-800 text-gray-500 dark:text-gray-400 flex items-center justify-center font-bold text-lg shadow-sm">
                        3</div>
                    <h3 class="text-xl font-semibold text-gray-900 dark:text-white">Execute</h3>
                    <p class="text-gray-600 dark:text-gray-400 text-sm leading-relaxed max-w-xs">Backtest refined
                        strategies. Execute with confidence knowing the probabilities are in your favor.</p>
                </div>
            </div>
        </section>

        <!-- Why Tradexy -->
        <section id="why-tradexy" class="py-24 bg-gray-50 dark:bg-[#0F0F0E] border-y border-gray-200 dark:border-[#1F1F1E]">
            <div class="max-w-7xl mx-auto px-6">
                <div class="text-center space-y-4 mb-16">
                    <h2 class="text-3xl font-bold text-gray-900 dark:text-white tracking-tight">Why Traders Trust
                        Tradexy</h2>
                    <p class="text-lg text-gray-600 dark:text-gray-400">Built by a trader, for traders. I understand the
                        journey.</p>
                </div>

                <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-8">
                    <!-- Privacy -->
                    <div class="flex flex-col items-center text-center p-6 rounded-2xl bg-transparent">
                        <div
                            class="w-14 h-14 bg-emerald-50 dark:bg-emerald-900/10 rounded-full flex items-center justify-center text-emerald-600 dark:text-emerald-400 mb-6">
                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"
                                fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                stroke-linejoin="round">
                                <rect width="18" height="11" x="3" y="11" rx="2" ry="2" />
                                <path d="M7 11V7a5 5 0 0 1 10 0v4" />
                            </svg>
                        </div>
                        <h3 class="text-lg font-semibold mb-2 text-gray-900 dark:text-white">Privacy First</h3>
                        <p class="text-gray-600 dark:text-gray-400 text-sm leading-relaxed">Your trading strategy is
                            your intellectual property. We prioritize security and do not sell your data.</p>
                    </div>

                    <!-- AI Integration -->
                    <div class="flex flex-col items-center text-center p-6 rounded-2xl bg-transparent">
                        <div
                            class="w-14 h-14 bg-violet-50 dark:bg-violet-900/10 rounded-full flex items-center justify-center text-violet-600 dark:text-violet-400 mb-6">
                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"
                                fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                stroke-linejoin="round">
                                <path
                                    d="m12 3-1.912 5.813a2 2 0 0 1-1.275 1.275L3 12l5.813 1.912a2 2 0 0 1 1.275 1.275L12 21l1.912-5.813a2 2 0 0 1 1.275-1.275L21 12l-5.813-1.912a2 2 0 0 1-1.275-1.275Z" />
                            </svg>
                        </div>
                        <h3 class="text-lg font-semibold mb-2 text-gray-900 dark:text-white">AI Integration</h3>
                        <p class="text-gray-600 dark:text-gray-400 text-sm leading-relaxed">Leverage Gemini AI for
                            unbiased market analysis and sentiment tracking, giving you an informational edge.</p>
                    </div>

                    <!-- Unified Workflow -->
                    <div class="flex flex-col items-center text-center p-6 rounded-2xl bg-transparent">
                        <div
                            class="w-14 h-14 bg-sky-50 dark:bg-sky-900/10 rounded-full flex items-center justify-center text-sky-600 dark:text-sky-400 mb-6">
                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"
                                fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                stroke-linejoin="round">
                                <rect width="7" height="9" x="3" y="3" rx="1" />
                                <rect width="7" height="5" x="14" y="3" rx="1" />
                                <rect width="7" height="9" x="14" y="12" rx="1" />
                                <rect width="7" height="5" x="3" y="16" rx="1" />
                            </svg>
                        </div>
                        <h3 class="text-lg font-semibold mb-2 text-gray-900 dark:text-white">Unified Workflow</h3>
                        <p class="text-gray-600 dark:text-gray-400 text-sm leading-relaxed">Stop managing 10 browser
                            tabs. Journal, backtest, news, and analysis—all in one streamlined dashboard.</p>
                    </div>
                </div>
            </div>
        </section>

        <!-- CTA -->
        <section class="max-w-7xl mx-auto px-6 py-24 text-center">
            <div class="bg-gray-900 dark:bg-[#161615] rounded-3xl p-12 lg:p-24 relative overflow-hidden">
                <div class="absolute inset-0 bg-gradient-to-br from-blue-600/20 to-purple-600/20 opacity-50"></div>
                <div class="relative z-10 space-y-6">
                    <h2 class="text-3xl md:text-5xl font-bold text-white tracking-tight">Ready to treat trading like a
                        business?</h2>
                    <p class="text-lg text-gray-300 max-w-2xl mx-auto">Join the platform built for traders who demand
                        data, consistency, and professional tools.</p>
                    <div class="pt-4">
                        <a href="{{ route('register') }}"
                            class="inline-flex items-center justify-center px-8 py-3 text-base font-semibold text-gray-900 bg-white rounded-lg hover:bg-gray-100 transition-colors">
                            Create Free Account
                        </a>
                    </div>
                </div>
            </div>
        </section>
    </main>

  
</x-layouts.app>