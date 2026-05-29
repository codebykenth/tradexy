# Tradexy — Trading Journal

A professional trading journal application built with Laravel, Tailwind CSS, and PostgreSQL. Log trades, backtest strategies, and leverage AI-powered insights to become a consistent, profitable trader.

---

## Tech Stack

- **Backend:** PHP 8.2+, Laravel 12
- **Frontend:** Blade, Tailwind CSS v4, Vite
- **Database:** PostgreSQL (Neon)
- **AI:** Google Gemini API
- **File storage:** Google Cloud Storage (production uploads)
- **Hosting:** [Vercel](https://vercel.com/) (serverless PHP via `vercel-php`)

---

## Prerequisites

- [PHP](https://www.php.net/) 8.2+ with extensions: `pdo_pgsql`, `mbstring`, `openssl`, `tokenizer`, `xml`, `ctype`, `json`, `bcmath`, `fileinfo`
- [Composer](https://getcomposer.org/)
- [Node.js](https://nodejs.org/) v20+
- [PostgreSQL](https://www.postgresql.org/) 16+ (local install or a hosted instance)
- [Git](https://git-scm.com/)

For deployment: a [Vercel](https://vercel.com/) account and a hosted PostgreSQL database.

---

## Local Development Setup

### 1. Clone the Repository

```bash
git clone https://github.com/codebykenth/trading-journal-v2.git
cd trading-journal-v2
```

### 2. Install Dependencies

```bash
composer install
npm install
```

### 3. Environment Variables

```bash
cp .env.example .env
```

Configure your database. Either set `DB_URL` (recommended for hosted Postgres) or individual fields:

```env
DB_CONNECTION=pgsql
DB_HOST=127.0.0.1
DB_PORT=5432
DB_DATABASE=tradexy
DB_USERNAME=your_user
DB_PASSWORD=your_password
```

Add API keys and other secrets as needed (`GEMINI_API_KEY`, OAuth credentials, etc.). See `.env.example` for the full list.

### 4. Application Key & Database

```bash
php artisan key:generate
php artisan migrate --seed
```

### 5. Run the Dev Stack

```bash
composer dev
```

This starts the Laravel dev server, queue worker, and Vite in one terminal. Open **http://127.0.0.1:8000**.

Alternatively, run services separately:

```bash
# Terminal 1
php artisan serve

# Terminal 2 (optional — skip if QUEUE_FORCE_SYNC=true)
php artisan queue:listen

# Terminal 3
npm run dev
```

---

## Daily Workflow

```bash
composer dev
```

Then open http://127.0.0.1:8000.

---

## Useful Commands

| Command | Description |
|---------|-------------|
| `composer dev` | Laravel server + queue + Vite (local dev) |
| `composer setup` | Fresh install: deps, key, migrate, build assets |
| `php artisan migrate` | Run migrations |
| `php artisan migrate:fresh --seed` | Reset DB & re-seed |
| `php artisan tinker` | Open Laravel REPL |
| `php artisan test` | Run tests |
| `php artisan cache:clear` | Clear application cache |
| `npm run dev` | Vite dev server (hot reload) |
| `npm run build` | Build production assets |

---

## Project Structure

```
trading-journal-v2/
├── api/
│   └── index.php           # Vercel serverless entry point
├── app/                    # Laravel application code
│   ├── Http/Controllers/   # Route controllers
│   ├── Models/             # Eloquent models
│   └── Services/           # Business logic
├── config/                 # Configuration files
├── database/
│   ├── migrations/         # Database migrations
│   └── seeders/            # Database seeders
├── public/                 # Public assets
├── resources/
│   ├── css/                # Stylesheets (Tailwind)
│   ├── js/                 # JavaScript
│   └── views/              # Blade templates
├── routes/                 # Route definitions
├── storage/                # Logs, cache, uploads
├── vercel.json             # Vercel build & routing config
├── vite.config.js          # Vite configuration
└── .env.example            # Environment template
```

---

## Environment Files

| File | Purpose |
|------|---------|
| `.env.example` | Template — committed to git |
| `.env` | Your local config — **never committed** |

Set secrets in the Vercel project dashboard for production (not in git).

---

## Deployment (Vercel)

The app is configured for Vercel via `vercel.json`. Static assets are served from `public/`; all other requests route to `api/index.php`.

### 1. Connect the Repository

1. Import the GitHub repo in the [Vercel dashboard](https://vercel.com/new).
2. Vercel reads `vercel.json` automatically (`buildCommand`, PHP runtime, routes).

### 2. Environment Variables

Add these in **Project → Settings → Environment Variables** (use production values):

| Variable | Notes |
|----------|-------|
| `APP_KEY` | From `php artisan key:generate` |
| `APP_ENV` | `production` |
| `APP_DEBUG` | `false` |
| `APP_URL` | Your Vercel or custom domain URL |
| `DB_URL` | Hosted PostgreSQL connection string |
| `QUEUE_FORCE_SYNC` | `true` — no background workers on Vercel |
| `SESSION_DRIVER` | `database` |
| `CACHE_STORE` | `database` |
| `GEMINI_API_KEY` | AI analysis |
| `GOOGLE_CLOUD_*` / `GCS_URL` | File uploads (GCS) |
| `BROADCAST_CONNECTION` | `pusher` for real-time updates |
| `REALTIME_ENABLED` | `true` to broadcast events & load Echo |
| `PUSHER_APP_*` | App ID, key, secret, cluster from [Pusher](https://pusher.com/) |
| OAuth vars | Update redirect URLs to your production domain |

Vercel sets `VERCEL=1` automatically; logging is configured to use stderr in that environment.

### 3. Database Migrations

Vercel does not run migrations on deploy. Run them against your production database before or after each release:

```bash
# With production DB_URL in your local .env, or via Vercel CLI:
php artisan migrate --force
```

### 4. Deploy

Push to the connected branch (usually `main`). Vercel builds frontend assets (`npm ci && npm run build`) and deploys the PHP function.

For a custom domain, add it under **Project → Settings → Domains** and update `APP_URL` and OAuth callback URLs accordingly.

---

## License

This project is proprietary software. All rights reserved.
