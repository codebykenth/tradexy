# Tradexy — Trading Journal

A professional trading journal application built with Laravel, Tailwind CSS, and PostgreSQL. Log trades, backtest strategies, and leverage AI-powered insights to become a consistent, profitable trader.

---

## Tech Stack

- **Backend:** PHP 8.4, Laravel 12
- **Frontend:** Blade, Tailwind CSS v4, Vite
- **Database:** PostgreSQL 16
- **AI:** Google Gemini API
- **Containerization:** Docker & Docker Compose

---

## Prerequisites

- [Docker Desktop](https://www.docker.com/products/docker-desktop/) (includes Docker Compose)
- [Node.js](https://nodejs.org/) (v20+ recommended) — for running Vite on the host
- [Git](https://git-scm.com/)

---

## Local Development Setup

### 1. Clone the Repository

```bash
git clone https://github.com/codebykenth/trading-journal-v2.git
cd trading-journal-v2
```

### 2. Install Node Dependencies

```bash
npm install
```

### 3. Setup Environment Variables

```bash
cp .env.example .env
```

Open `.env` and configure the database to use the Docker PostgreSQL container:

```env
DB_CONNECTION=pgsql
DB_HOST=postgres
DB_PORT=5432
DB_DATABASE=tradexy
DB_USERNAME=tradexy
DB_PASSWORD=secret
```

> **Note:** `DB_HOST=postgres` refers to the Docker service name, not `localhost`. The PHP app container connects to the Postgres container over the Docker network.

### 4. Start Docker Containers

```bash
docker-compose up -d --build
```

This starts:
| Service | Container | Access |
|---------|-----------|--------|
| **PHP-FPM** | `laravel_app` | — |
| **Nginx** | `laravel_nginx` | http://localhost:8000 |
| **PostgreSQL** | `laravel_postgres` | localhost:5432 |

### 5. Generate App Key

```bash
docker-compose exec app php artisan key:generate
```

### 6. Run Migrations & Seeders

```bash
docker-compose exec app php artisan migrate --seed
```

### 7. Start Vite (Frontend Assets)

In a **separate terminal**, run Vite on the host for instant hot reload:

```bash
npm run dev
```

> **Why on the host?** Running Vite inside Docker on Windows causes ~10 second delays due to volume sync. Running natively gives instant file detection and hot reload.

### 8. Open the App

Visit **http://localhost:8000** in your browser.

---

## Daily Workflow

Once the initial setup is done, your daily workflow is:

```bash
# Terminal 1 — Start Docker (PHP, Nginx, Postgres)
docker-compose up -d

# Terminal 2 — Start Vite (hot reload)
npm run dev
```

Then open http://localhost:8000 and start developing!

---

## Useful Commands

| Command | Description |
|---------|-------------|
| `docker-compose up -d` | Start all containers in background |
| `docker-compose down` | Stop all containers |
| `docker-compose exec app php artisan migrate` | Run migrations |
| `docker-compose exec app php artisan migrate:fresh --seed` | Reset DB & re-seed |
| `docker-compose exec app php artisan tinker` | Open Laravel REPL |
| `docker-compose exec app php artisan test` | Run tests |
| `docker-compose exec app php artisan cache:clear` | Clear application cache |
| `docker-compose logs -f app` | View PHP container logs |
| `npm run dev` | Start Vite dev server (hot reload) |
| `npm run build` | Build production assets |

---

## Project Structure

```
trading-journal-v2/
├── app/                    # Laravel application code
│   ├── Http/Controllers/   # Route controllers
│   ├── Models/             # Eloquent models
│   └── Services/           # Business logic
├── config/                 # Configuration files
├── database/
│   ├── migrations/         # Database migrations
│   └── seeders/            # Database seeders
├── docker/
│   ├── nginx/              # Nginx configuration
│   └── php/                # PHP Dockerfile
├── public/                 # Public assets
│   └── images/             # Static images
├── resources/
│   ├── css/                # Stylesheets (Tailwind)
│   ├── js/                 # JavaScript
│   └── views/              # Blade templates
├── routes/                 # Route definitions
├── storage/                # Logs, cache, uploads
├── docker-compose.yml      # Local dev Docker config
├── vite.config.js          # Vite configuration
└── .env.example            # Environment template
```

---

## Environment Files

| File | Purpose |
|------|---------|
| `.env.example` | Template — committed to git |
| `.env` | Your local config — **never committed** |

---

## Deployment

For full deployment instructions (server setup, CI/CD, SSL, domains), see **[deployment-guide.md](deployment-guide.md)**.

---

## License

This project is proprietary software. All rights reserved.
