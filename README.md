# Tradexy — Trading Journal & Analytics

Tradexy is a web-based trading journal built for Crypto (USDT Perps & Spot) and Philippine Stock Exchange (PSE) traders. It helps traders log trades, backtest strategies, track equity curves, scan market setups, and get AI-assisted feedback on their charts.

---

## What It Does

Most trading journals either lack support for local stock exchange fees (like PSE's transfer fees, commissions, and taxes) or don't offer automated chart analysis. Tradexy solves this by combining:

- **Dual-Market Support:** Accurate PnL calculations for Crypto leverage trading and PSE equity trades with all required Philippine regulatory fees (SCCP, transfer fee, broker commission, sales tax, VAT).
- **AI Chart Feedback:** Upload a chart screenshot and get an automated breakdown from Google Gemini covering setup quality, risk-to-reward ratio, entry/exit timing, and execution score.
- **AI Market Insights (Daily News):** Automated daily macro market reports and sentiment analysis covering Gold, Crypto, CPI inflation data, and Federal Reserve interest rate trends.
- **Market Screener:** Technical screening tool to monitor active setups, breakout patterns, and volume across crypto and equities.
- **Strategy Playbooks & Rules:** Create custom strategies with checklists for entry, exit, risk management, and scaling to verify rule compliance per trade.
- **PnL Calendar & Balance History:** Visual green/red day calendar heatmaps and equity curves to track consistency over time.
- **CSV Import & Export Engine:** Bulk import historical trades and balances with automatic deduplication by Order ID / trade signature, plus streaming CSV export that respects active filters.
- **Public Trade Sharing:** Generate shareable tokenized links (`/shared/trades/{token}`) to share setups and charts without exposing account balances or personal info.
- **Automated Bybit Sync:** Background tasks that pull closed PnL and wallet balances directly via Bybit API.
- **Demo & Real Mode:** Toggle between Live and Demo journaling with isolated caching and distinct analytics.

---

## Tech Stack

- **Backend:** PHP 8.2+, Laravel 12
- **Frontend:** Blade, Tailwind CSS v4, DaisyUI, Hotwire Turbo Drive
- **Database:** PostgreSQL (Neon / Local)
- **AI Analysis:** Google Gemini API (1.5 Flash / Pro)
- **Real-time:** Pusher WebSockets + Laravel Echo
- **Storage:** Google Cloud Storage / Firebase (for chart images)
- **Deployment:** Vercel (serverless PHP via `vercel-php`)

---

## Architecture & Code Highlights

- **Thin Controllers:** Public controller methods stay under ~10 lines, delegating validation to FormRequests and business logic to dedicated Services (`TradeImportExportService`, `DailyNewsService`, `BybitService`, `ScreenerService`).
- **Global Transaction Middleware:** Relational multi-table writes (trades + entry reasons + lessons) are wrapped by `TransactionalRequest` middleware for ACID integrity.
- **Dynamic MD5 Query Caching:** Cached index queries use MD5 parameter hashes (`trades_v..._f{filterHash}`) to deliver sub-millisecond responses without stale cache conflicts across filter combinations.
- **Idempotent Syncing:** Relation updates (reasons, rules, lessons) follow a delete-then-recreate pattern to avoid duplicate records on resubmission.
- **Strict Typing:** `declare(strict_types=1)` across all PHP files with PHPStan Level 5 static analysis compliance.

---

## Full Feature Breakdown

### 1. Trade Logs & Setup Gallery
- **Multi-Filter Toolbar:** Filter trades by Symbol/Ticker, Outcome (Wins, Losses, Breakeven), Direction (Long, Short), Strategy, Timeframe, Date Range, and media tags (Chart Attached, AI Analyzed).
- **Dual Views:** Switch between an interactive table view with 1-click compact bulk actions (bulk timeframe/strategy update, bulk delete) and a visual card gallery displaying chart screenshots.
- **Psychology Tracking:** Log entry and exit emotional states (e.g. FOMO, Confident, Anxious, Fearful) alongside self-reflection lessons.

### 2. AI Trade Critique & Setup Post-Mortem
- Request on-demand Gemini AI analysis for any trade with an attached chart screenshot.
- Returns a structured post-mortem: Market context, intended edge validity, stop-loss placement critique, psychological root causes, action items, and an execution score (1–10).
- **Rate Limit:** 1 AI analysis per day for regular users (`throttle:ai-analysis`); unlimited for admin/developer accounts (`User::id === 1` or `is_admin === true`).

### 3. AI Market Insights (Daily News)
- **Automated Macro Analysis:** Automated daily background process (`generate:daily-news`) that aggregates economic RSS feeds and prompts Gemini to synthesize macro reports.
- **Covered Markets:** Gold, Bitcoin & Crypto, Federal Reserve monetary policy, CPI/PPI inflation prints, and global liquidity trends.
- Dedicated `/insights` view with historical archive and latest daily breakdown.

### 4. Technical Market Screener
- Accessible via `/screener` to filter and monitor real-time market setups, technical indicators, and price action patterns across tracked assets.

### 5. CSV/Excel Import & Export Engine
- **Trade Import & Export:** Drag-and-drop CSV uploader with downloadable template, spreadsheet preview, and duplicate resolution (skips duplicate Order IDs or composite signatures). Filter-aware CSV exports preserve active table filters.
- **Balance Import & Export:** Bulk import/export daily equity curve entries with duplicate prevention and downloadable balance CSV template.

### 6. Strategy Playbooks & Rule Checklists
- Define strategies with target R:R, max risk percentage, category, and color tags.
- Attach structured checklist rules categorized into **Entry**, **Exit**, **Risk Management**, and **Scaling**.
- Calculate win rate, profit factor, average return, and net PnL per strategy.

### 7. PnL Calendar & Portfolio Analytics
- Interactive monthly calendar heatmap displaying daily net PnL, win/loss trade badges, and day-by-day modal breakdowns.
- Cumulative equity curve tracking wallet balance and total portfolio value over time.

### 8. Tokenized Public Trade Sharing
- Generate cryptographically secure public links (`/shared/trades/{token}`) to share trade breakdowns on social media or with mentors.
- Privacy-safe: hides account balances, private trades, and user identity.
- Instant 1-click link revocation.

### 9. Trading Modes & Preferences
- **Instant Workspace Switch:** Toggle between **Live** and **Demo** environments. Caches and calculations remain strictly separated.
- **Currency Preferences:** Switch between **USD** and **PHP** displays.

### 10. Authentication, Administration & Compliance
- **Social Login:** Sign in via **Google** or **GitHub** OAuth (via Laravel Socialite) or email/password.
- **Terms & Privacy Enforcement:** Mandatory Terms of Service agreement with modal gating on first login (`terms_accepted_at`).
- **Admin Dashboard (`/admin`):** User management, real-time activity audit logs, maintenance mode toggling, and cache flush controls.

---

## 🔌 Third-Party API Integrations

### 1. Google Gemini AI API
- **Multimodal Chart Setup Analysis (`app/Jobs/AnalyzeTradeJob.php`):** Downloads the trade chart screenshot, encodes it into Base64, and sends it with trade execution parameters (entry/exit, R:R, emotions, user lessons) to Gemini (`gemini-3-flash-preview:generateContent`) to generate an execution score and risk critique. Triggered via `app/Http/Controllers/AiAnalysisController.php`.
- **Daily Macro News (`app/Services/DailyNewsService.php`):** Pulls economic RSS feeds and prompts Gemini to produce structured daily macro reports. Run via `php artisan generate:daily-news`.

### 2. Bybit V5 Exchange API
- **Bybit Client (`app/Services/BybitService.php`):** Implements Bybit V5 HMAC-SHA256 signature authentication (`X-BAPI-SIGN`, `X-BAPI-TIMESTAMP`, `X-BAPI-API-KEY`, `X-BAPI-RECV-WINDOW`) to query closed PnL (`/v5/position/closed-pnl`) and wallet balances (`/v5/account/wallet-balance`).
- **Automated Sync Scope (`app/Console/Commands/FetchClosedPnl.php`, `FetchBalance.php`):** Server-side background cron jobs scheduled in `routes/console.php`. Runs automatically for the designated admin user configured via `BYBIT_USER_EMAIL` in `.env` (restricted to Bybit Linear USDT perpetual contracts).

### 3. Google & GitHub OAuth (Socialite)
- **OAuth Controller (`app/Http/Controllers/SocialiteController.php`):** Secure authentication flow using Google and GitHub providers with automatic user account provisioning.

### 4. Pusher & Laravel Echo (Real-Time WebSockets)
- **Event Broadcasting (`app/Events/TradeAnalysisGenerated.php`):** Broadcasts completed AI analyses over private user channels to automatically update the frontend without manual refreshes.

### 5. Cloud Storage (Google Cloud Storage / Firebase)
- **File Upload Service (`app/Services/FileService.php`):** Handles image validation, MIME detection, and cloud uploads for high-resolution trade setup chart screenshots.

---

## Local Development Setup

### 1. Requirements
- PHP 8.2 or higher (with `pdo_pgsql`, `mbstring`, `bcmath`, `fileinfo`)
- Composer
- Node.js 20+ & npm
- PostgreSQL 16+

### 2. Install

```bash
# Clone the repository
git clone https://github.com/codebykenth/tradexy.git
cd trading-journal-v2

# Install backend & frontend dependencies
composer install
npm install
```

### 3. Environment Config

```bash
cp .env.example .env
php artisan key:generate
```

Update your database credentials in `.env`:

```env
DB_CONNECTION=pgsql
DB_HOST=127.0.0.1
DB_PORT=5432
DB_DATABASE=tradexy
DB_USERNAME=postgres
DB_PASSWORD=your_password
```

*(Optional API keys)*
```env
GEMINI_API_KEY=your_key_here
PUSHER_APP_KEY=your_key_here
```

### 4. Database Setup & Seed

```bash
php artisan migrate:fresh --seed
```

This sets up the tables and seeds a demo account:
- **Email:** `demo@tradexy.app`
- **Password:** `password`

### 5. Run the Application

```bash
composer dev
```

This starts the Laravel server, queue listener, and Vite hot-reload. Navigate to `http://127.0.0.1:8000`.

---

## Automated Tests & Code Quality

```bash
# Run feature & unit test suite (Pest PHP)
php artisan test

# Run PHPStan static analysis
./vendor/bin/phpstan analyse --memory-limit=2G

# Run code style fixer (Pint)
./vendor/bin/pint
```

---

## Deployment (Vercel)

The project is structured for serverless deployment on Vercel using `vercel.json` and the `vercel-php` runtime:
- Web traffic routes through `api/index.php`.
- Static assets are served from `public/`.
- PostgreSQL connects to hosted instances (Neon, Supabase, AWS RDS).

To deploy:
1. Connect the repository in the Vercel dashboard.
2. Add the environment variables from `.env.example`.
3. Run `php artisan migrate --force` against your production database.

---

## Author

Built by [Kenth](https://github.com/codebykenth).
