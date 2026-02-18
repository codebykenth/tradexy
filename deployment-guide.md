# Tradexy — Complete Deployment Guide

> **Last updated:** February 18, 2026
> **Server:** xCloud Managed VPS (single server, 3 environments)
> **Domain:** `tradexy.site` with SSL via Let's Encrypt
> **Database:** Aiven PostgreSQL (dev), Supabase PostgreSQL (staging/prod)

---

## Table of Contents

1. [Security Best Practices](#security)
2. [How It Works](#how-it-works)
3. [Running Locally](#running-locally)
4. [File Reference — What Each File Does](#file-reference)
5. [GitHub Setup (One-Time)](#github-setup)
6. [Server Setup (One-Time)](#server-setup)
7. [Domain & SSL Setup (One-Time)](#domain-setup)
8. [Deploying](#deploying)
9. [Verifying a Deployment](#verifying)
10. [Manual Deploy (deploy.sh)](#manual-deploy)
11. [Disk Cleanup](#disk-cleanup)
12. [Troubleshooting](#troubleshooting)

---

## 1. Security Best Practices <a id="security"></a>

> ⚠️ **This file is committed to git.** Never put real credentials, IPs, or secrets in this file.

- **Never commit `.env` files** — they contain APP_KEY, DB passwords, API keys. The `.gitignore` excludes them.
- **Never expose your server IP** in committed files — use placeholders like `<YOUR_SERVER_IP>`.
- **Use GitHub Secrets** for all sensitive CI/CD values (SSH keys, tokens, server IPs).
- **Disable password SSH login** — use SSH key-only authentication:
  ```bash
  # On your server, edit /etc/ssh/sshd_config:
  PasswordAuthentication no
  PermitRootLogin prohibit-password
  # Then restart: sudo systemctl restart sshd
  ```
- **Restrict firewall** — only open ports 80 (HTTP), 443 (HTTPS), and SSH:
  ```bash
  ufw default deny incoming
  ufw default allow outgoing
  ufw allow ssh
  ufw allow 80       # HTTP (for Certbot + redirect)
  ufw allow 443      # HTTPS
  ufw enable
  ```
- **Docker ports bound to localhost** — compose files use `127.0.0.1:808x:80` so containers are NOT directly accessible from the internet. Only the host Nginx reverse proxy can reach them.
- **Keep Docker images minimal** — the `.dockerignore` ensures secrets and unnecessary files stay out of the image.
- **Use separate APP_KEYs** per environment — never share the same key across dev/staging/prod.
- **Rotate GH_PAT periodically** — set an expiration when creating your GitHub Personal Access Token.

---

## 2. How It Works <a id="how-it-works"></a>

```
You push code → GitHub Actions runs tests → Builds Docker image → Pushes to GHCR
               → SCPs compose+nginx config to server
               → SSHs into server → Pulls image → Restarts containers
               → Host Nginx reverse proxy routes domain traffic to containers
```

| Branch | GHCR Tag | Server Dir | Internal Port | Domain | Database |
|--------|----------|------------|---------------|--------|----------|
| `dev` | `:dev` | `/var/www/tradexy-dev` | 127.0.0.1:8080 | `https://dev.tradexy.site` | Aiven |
| `staging` | `:staging` | `/var/www/tradexy-staging` | 127.0.0.1:8081 | `https://staging.tradexy.site` | Supabase |
| `main` | `:main` | `/var/www/tradexy-prod` | 127.0.0.1:8082 | `https://tradexy.site` | Supabase |

**Architecture:**
```
Internet → tradexy.site → Host Nginx (port 443, SSL) → 127.0.0.1:8082 → Docker Nginx → PHP-FPM App
         → dev.tradexy.site → Host Nginx (port 443, SSL) → 127.0.0.1:8080 → Docker Nginx → PHP-FPM App
         → staging.tradexy.site → Host Nginx (port 443, SSL) → 127.0.0.1:8081 → Docker Nginx → PHP-FPM App
```

---

## 3. Running Locally <a id="running-locally"></a>

This project includes a Docker environment for local development.

### Prerequisites
- Docker & Docker Compose
- Git

### Quick Start

1. **Clone the repository:**
   ```bash
   git clone <repo-url>
   cd trading-journal-v2
   ```

2. **Setup Environment Variables:**
   ```bash
   cp .env.example .env
   ```
   Open `.env` and set the following variables to match the Docker configuration:
   ```env
   DB_CONNECTION=pgsql
   DB_HOST=postgres
   DB_PORT=5432
   DB_DATABASE=tradexy
   DB_USERNAME=tradexy
   DB_PASSWORD=secret
   ```

3. **Start Docker:**
   ```bash
   docker-compose up -d --build
   ```
   This will start:
   - **Nginx & PHP:** Application server (http://localhost:8000)
   - **Postgres:** Database
   - **Vite:** Asset server (http://localhost:5173). Runs `npm run dev` automatically.

4. **Initialize Database:**
   ```bash
   docker-compose exec app php artisan migrate --seed
   ```

5. **Access the App:**
   - App: http://localhost:8000
   - Vite: http://localhost:5173

### Managing Frontend (Vite)
- **Logs:** `docker logs -f laravel_vite`
- **Install Packages:**
  ```bash
  docker-compose exec vite npm install <package-name>
  docker-compose restart vite
  ```

---

## 4. File Reference — What Each File Does <a id="file-reference"></a>

### `.dockerignore`
**Purpose:** Tells Docker what to EXCLUDE when building the image. Without this, `COPY . .` in the Dockerfile would copy `.git` (several GB), `node_modules`, `vendor`, etc., making the image huge.

```
.git                          # Git history — not needed in production
.github                       # GitHub Actions — not needed in image
.env / .env.*                 # Secrets — NEVER bake into image
!.env.example                 # Exception: keep the example file
node_modules                  # Frontend deps — not needed in PHP image
vendor                        # Rebuilt by composer install during build
storage/logs/*                # Runtime logs — not needed in image
storage/framework/cache/*     # Runtime cache
storage/framework/sessions/*  # Runtime sessions
storage/framework/views/*     # Compiled views
tests                         # Test files — not needed in production
phpunit.xml                   # Test config
docker-compose*.yml           # Compose files — deployed separately via SCP
deploy.sh                     # Deploy script
deployment-guide.md           # This file
README.md                     # Readme
.gitignore / .dockerignore    # Config files
.vscode / .idea               # IDE files
```

---

### `docker/php/Dockerfile` (Development — local only)
**Purpose:** Builds the PHP container for **local development**. Not used on the server.

```dockerfile
FROM php:8.4-fpm

# Install PHP extensions needed for Laravel + PostgreSQL
RUN apt-get update && apt-get install -y \
    git curl zip unzip libpng-dev libonig-dev \
    libxml2-dev libzip-dev libpq-dev \
    && docker-php-ext-install \
        pdo pdo_pgsql mbstring zip exif pcntl

# Install Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

WORKDIR /var/www
COPY . .

# Set permissions
RUN chown -R www-data:www-data /var/www \
    && chmod -R 775 storage bootstrap/cache

# Install ALL dependencies (including dev)
RUN composer install --no-interaction --prefer-dist

EXPOSE 9000
CMD ["php-fpm"]
```

---

### `docker/php/Dockerfile.prod` (Production — used by CI/CD)
**Purpose:** Builds the optimized PHP container image that gets pushed to GHCR. This is the image used on the server for ALL environments (dev/staging/prod).

```dockerfile
FROM php:8.4-fpm

# Install system dependencies + PHP extensions
RUN apt-get update && apt-get install -y \
    git curl zip unzip libpng-dev libonig-dev \
    libxml2-dev libzip-dev libpq-dev libfcgi-bin \
    && docker-php-ext-install \
        pdo pdo_pgsql mbstring zip exif pcntl \
    && apt-get clean && rm -rf /var/lib/apt/lists/*    # Clean up to reduce size

# Health check script (for monitoring)
RUN curl -o /usr/local/bin/php-fpm-healthcheck \
    https://raw.githubusercontent.com/renatomefi/php-fpm-healthcheck/master/php-fpm-healthcheck \
    && chmod +x /usr/local/bin/php-fpm-healthcheck
RUN echo "pm.status_path = /status" >> /usr/local/etc/php-fpm.d/zz-docker.conf

COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

WORKDIR /var/www

# Layer caching: copy composer files first, install deps, then copy code
# This means deps are only re-installed if composer.json/lock changes
COPY composer.json composer.lock ./
RUN composer install --no-dev --optimize-autoloader --no-interaction --prefer-dist --no-scripts

# Copy application code (filtered by .dockerignore)
COPY . .

# Rebuild autoloader with full code
RUN composer dump-autoload --optimize

# Set permissions
RUN chown -R www-data:www-data /var/www \
    && chmod -R 775 storage bootstrap/cache

EXPOSE 9000
CMD ["php-fpm"]
```

**Key differences from dev Dockerfile:**
- `--no-dev` — excludes dev dependencies
- `--optimize-autoloader` — faster class loading
- Layer caching — composer install runs before code copy
- Health check — for container monitoring
- `apt-get clean` — smaller image

---

### `docker/nginx/default.conf` (Inside Docker containers)
**Purpose:** Nginx config inside each Docker container. Routes HTTP requests to PHP-FPM. This file is SCP'd to the server during deployment.

```nginx
server {
    listen 80;
    index index.php index.html;
    root /var/www/public;            # Laravel's public directory

    location / {
        try_files $uri $uri/ /index.php?$query_string;    # Pretty URLs
    }

    location ~ \.php$ {
        fastcgi_pass app:9000;       # Forward PHP to the app container
        fastcgi_index index.php;
        fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
        include fastcgi_params;
    }

    location ~ /\.ht {
        deny all;                    # Block .htaccess files
    }
}
```

---

### `docker/nginx/tradexy-prod.conf` (Host reverse proxy — production)
**Purpose:** Host-level Nginx config that reverse-proxies `tradexy.site` to Docker container on port 8082. Installed on the server at `/etc/nginx/sites-available/`. Certbot auto-adds the SSL block.

```nginx
server {
    listen 80;
    server_name tradexy.site www.tradexy.site;

    location / {
        proxy_pass http://127.0.0.1:8082;
        proxy_set_header Host $host;
        proxy_set_header X-Real-IP $remote_addr;
        proxy_set_header X-Forwarded-For $proxy_add_x_forwarded_for;
        proxy_set_header X-Forwarded-Proto $scheme;
        proxy_http_version 1.1;
        proxy_set_header Upgrade $http_upgrade;
        proxy_set_header Connection "upgrade";
    }
    client_max_body_size 64M;
}
```

### `docker/nginx/tradexy-staging.conf` & `docker/nginx/tradexy-dev.conf`
**Purpose:** Same as above but for `staging.tradexy.site` (port 8081) and `dev.tradexy.site` (port 8080).

---

### `docker-compose.yml` (Base — local development only)
**Purpose:** For running the app locally on your machine with a local PostgreSQL. **NOT used on the server.**

```yaml
services:
  app:
    build:
      context: .
      dockerfile: docker/php/Dockerfile    # Builds from source locally
    container_name: laravel_app
    volumes:
      - .:/var/www                         # Live code mount for hot reload
    networks:
      - laravel

  nginx:
    image: nginx:alpine
    container_name: laravel_nginx
    ports:
      - "8000:80"                          # Access at localhost:8000
    volumes:
      - .:/var/www
      - ./docker/nginx/default.conf:/etc/nginx/conf.d/default.conf
    depends_on:
      - app
    networks:
      - laravel

  postgres:
    image: postgres:16
    container_name: laravel_postgres
    restart: unless-stopped
    environment:
      POSTGRES_DB: ${DB_DATABASE}
      POSTGRES_USER: ${DB_USERNAME}
      POSTGRES_PASSWORD: ${DB_PASSWORD}
    ports:
      - "5432:5432"
    volumes:
      - pgdata:/var/lib/postgresql/data
    networks:
      - laravel

networks:
  laravel:

volumes:
  pgdata:
```

---

### `docker-compose.dev.yml` (Dev server)
**Purpose:** Runs the app on the server for the **dev** environment. Pulls pre-built image from GHCR. No local PostgreSQL (uses Aiven). Port bound to localhost only.

```yaml
services:
  app:
    image: ghcr.io/codebykenth/trading-journal-v2:dev
    container_name: tradexy_app_dev
    restart: unless-stopped
    working_dir: /var/www
    env_file: .env                                       # ← Loads .env into the container
    volumes:
      - app_storage_dev:/var/www/storage
    networks:
      - tradexy_dev

  nginx:
    image: nginx:alpine
    container_name: tradexy_nginx_dev
    restart: unless-stopped
    ports:
      - "127.0.0.1:8080:80"                             # ← Localhost only! Not exposed to internet
    volumes:
      - app_storage_dev:/var/www/storage:ro
      - ./docker/nginx/default.conf:/etc/nginx/conf.d/default.conf:ro
    depends_on:
      - app
    networks:
      - tradexy_dev

networks:
  tradexy_dev:

volumes:
  app_storage_dev:
```

---

### `docker-compose.staging.yml` (Staging server)
**Purpose:** Same as dev but for **staging**. Port `127.0.0.1:8081`. Uses Supabase DB. Tag `:staging`.

### `docker-compose.prod.yml` (Production server)
**Purpose:** Same but for **production**. Port `127.0.0.1:8082`. Uses Supabase DB. Tag `:main`.

---

### `.github/workflows/ci-cd.yml`
**Purpose:** The CI/CD pipeline. Triggered on every push to `dev`, `staging`, or `main`. Has 5 jobs:

| Job | What it does |
|-----|-------------|
| **1. Test** | Installs PHP 8.4, runs `composer install`, creates test DB, runs `php artisan test` |
| **2. Build & Push** | Builds Docker image using `Dockerfile.prod`, pushes to `ghcr.io/codebykenth/trading-journal-v2:<branch>` |
| **3. Deploy Dev** | SCPs compose+nginx to server, pulls image, restarts containers, runs migrations, clears cache |
| **4. Deploy Staging** | Same but caches config/routes/views (optimized for performance) |
| **5. Deploy Prod** | Same as staging |

**GitHub Secrets required:**
| Secret | Value |
|--------|-------|
| `SERVER_HOST` | Your VPS IP address |
| `SERVER_USERNAME` | SSH username (e.g. `root`) |
| `SERVER_SSH_KEY` | Your private SSH key (full content) |
| `SERVER_SSH_PASSPHRASE` | SSH key passphrase (if any) |
| `GH_PAT` | GitHub Personal Access Token with `read:packages`, `write:packages` scopes |

---

### `deploy.sh` (Manual deploy script)
**Purpose:** Optional script for manually deploying from the server terminal instead of using CI/CD. Useful for emergency deploys or debugging.

Usage (on the server):
```bash
./deploy.sh dev
./deploy.sh staging
./deploy.sh production
```

---

## 5. GitHub Setup (One-Time) <a id="github-setup"></a>

### Step 1: Set workflow permissions
1. Go to your repo → **Settings** → **Actions** → **General**
2. Scroll to **Workflow permissions**
3. Select **"Read and write permissions"**
4. Check **"Allow GitHub Actions to create and approve pull requests"**
5. Click **Save**

### Step 2: Add repository secrets
1. Go to your repo → **Settings** → **Secrets and variables** → **Actions**
2. Click **New repository secret** for each:

| Secret Name | Value |
|------------|--------|
| `SERVER_HOST` | Your VPS IP address |
| `SERVER_USERNAME` | `root` |
| `SERVER_SSH_KEY` | Paste full content of your private SSH key |
| `SERVER_SSH_PASSPHRASE` | Your SSH key passphrase (if any) |
| `GH_PAT` | GitHub Personal Access Token |

### Step 3: Create GitHub PAT (if not done)
1. Go to [github.com/settings/tokens](https://github.com/settings/tokens)
2. Click **Generate new token (classic)**
3. Select scopes: `read:packages`, `write:packages`
4. Set an expiration date
5. Copy the token → add as `GH_PAT` secret

---

## 6. Server Setup (One-Time) <a id="server-setup"></a>

SSH into your server:
```bash
ssh -i ~/.ssh/<YOUR_KEY> root@<YOUR_SERVER_IP>
```

### Step 1: Create directories for all 3 environments

```bash
mkdir -p /var/www/tradexy-dev/docker/nginx
mkdir -p /var/www/tradexy-staging/docker/nginx
mkdir -p /var/www/tradexy-prod/docker/nginx
```

### Step 2: Create `.env` for dev

```bash
nano /var/www/tradexy-dev/.env
```

```env
APP_NAME=Tradexy
APP_ENV=local
APP_KEY=base64:PASTE_YOUR_KEY_HERE
APP_DEBUG=true
APP_URL=https://dev.tradexy.site

DB_CONNECTION=pgsql
DB_HOST=your-aiven-host.aivencloud.com
DB_PORT=12345
DB_DATABASE=your_db_name
DB_USERNAME=your_username
DB_PASSWORD=your_password

# Add all other env vars (mail, social auth, API keys, etc.)
```

> **Generate APP_KEY locally:**
> ```bash
> php artisan key:generate --show
> ```

### Step 3: Create `.env` for staging

```bash
nano /var/www/tradexy-staging/.env
```

```env
APP_NAME=Tradexy
APP_ENV=staging
APP_KEY=base64:PASTE_YOUR_KEY_HERE
APP_DEBUG=false
APP_URL=https://staging.tradexy.site

DB_CONNECTION=pgsql
DB_HOST=your-supabase-host.supabase.co
DB_PORT=5432
DB_DATABASE=postgres
DB_USERNAME=your_username
DB_PASSWORD=your_password
```

### Step 4: Create `.env` for production

```bash
nano /var/www/tradexy-prod/.env
```

```env
APP_NAME=Tradexy
APP_ENV=production
APP_KEY=base64:PASTE_YOUR_KEY_HERE
APP_DEBUG=false
APP_URL=https://tradexy.site

DB_CONNECTION=pgsql
DB_HOST=your-supabase-host.supabase.co
DB_PORT=5432
DB_DATABASE=postgres
DB_USERNAME=your_username
DB_PASSWORD=your_password
```

### Step 5: Set permissions

```bash
chown -R $USER:$USER /var/www/tradexy-dev
chown -R $USER:$USER /var/www/tradexy-staging
chown -R $USER:$USER /var/www/tradexy-prod
```

### Step 6: Configure firewall

```bash
ufw default deny incoming
ufw default allow outgoing
ufw allow ssh
ufw allow 80       # HTTP (for Certbot + redirect to HTTPS)
ufw allow 443      # HTTPS
ufw enable
```

> **Note:** Ports 8080/8081/8082 do NOT need to be opened. They are bound to `127.0.0.1` (localhost only) and accessed via the host Nginx reverse proxy.

### Step 7: Verify directory structure

After the first CI/CD deploy, each directory should look like:
```
/var/www/tradexy-dev/
├── .env                          ← You created this (Step 2)
├── docker-compose.dev.yml        ← Auto-synced by CI/CD
└── docker/
    └── nginx/
        └── default.conf          ← Auto-synced by CI/CD
```

---

## 7. Domain & SSL Setup (One-Time) <a id="domain-setup"></a>

### Step 1: Configure DNS at your domain registrar

Add these DNS records pointing to your server IP:

| Type | Name | Value |
|------|------|-------|
| **A** | `@` | `<YOUR_SERVER_IP>` |
| **CNAME** | `www` | `tradexy.site` |
| **CNAME** | `dev` | `tradexy.site` |
| **CNAME** | `staging` | `tradexy.site` |

> DNS propagation takes 5–30 minutes. Check with `ping tradexy.site`.

### Step 2: Install host Nginx & Certbot

```bash
# If apt complains about MySQL repo:
rm -f /etc/apt/sources.list.d/mysql*.list

apt update && apt install -y nginx certbot python3-certbot-nginx
```

### Step 3: Create reverse proxy configs on the server

```bash
# Production: tradexy.site → Docker port 8082
cat > /etc/nginx/sites-available/tradexy-prod << 'EOF'
server {
    listen 80;
    server_name tradexy.site www.tradexy.site;

    location / {
        proxy_pass http://127.0.0.1:8082;
        proxy_set_header Host $host;
        proxy_set_header X-Real-IP $remote_addr;
        proxy_set_header X-Forwarded-For $proxy_add_x_forwarded_for;
        proxy_set_header X-Forwarded-Proto $scheme;
        proxy_http_version 1.1;
        proxy_set_header Upgrade $http_upgrade;
        proxy_set_header Connection "upgrade";
        proxy_connect_timeout 60s;
        proxy_send_timeout 60s;
        proxy_read_timeout 60s;
    }
    client_max_body_size 64M;
}
EOF

# Staging: staging.tradexy.site → Docker port 8081
cat > /etc/nginx/sites-available/tradexy-staging << 'EOF'
server {
    listen 80;
    server_name staging.tradexy.site;

    location / {
        proxy_pass http://127.0.0.1:8081;
        proxy_set_header Host $host;
        proxy_set_header X-Real-IP $remote_addr;
        proxy_set_header X-Forwarded-For $proxy_add_x_forwarded_for;
        proxy_set_header X-Forwarded-Proto $scheme;
        proxy_http_version 1.1;
        proxy_set_header Upgrade $http_upgrade;
        proxy_set_header Connection "upgrade";
        proxy_connect_timeout 60s;
        proxy_send_timeout 60s;
        proxy_read_timeout 60s;
    }
    client_max_body_size 64M;
}
EOF

# Dev: dev.tradexy.site → Docker port 8080
cat > /etc/nginx/sites-available/tradexy-dev << 'EOF'
server {
    listen 80;
    server_name dev.tradexy.site;

    location / {
        proxy_pass http://127.0.0.1:8080;
        proxy_set_header Host $host;
        proxy_set_header X-Real-IP $remote_addr;
        proxy_set_header X-Forwarded-For $proxy_add_x_forwarded_for;
        proxy_set_header X-Forwarded-Proto $scheme;
        proxy_http_version 1.1;
        proxy_set_header Upgrade $http_upgrade;
        proxy_set_header Connection "upgrade";
        proxy_connect_timeout 60s;
        proxy_send_timeout 60s;
        proxy_read_timeout 60s;
    }
    client_max_body_size 64M;
}
EOF
```

### Step 4: Enable the sites

```bash
ln -sf /etc/nginx/sites-available/tradexy-prod /etc/nginx/sites-enabled/
ln -sf /etc/nginx/sites-available/tradexy-staging /etc/nginx/sites-enabled/
ln -sf /etc/nginx/sites-available/tradexy-dev /etc/nginx/sites-enabled/

# Remove default site
rm -f /etc/nginx/sites-enabled/default

# Test and reload
nginx -t
systemctl reload nginx
```

### Step 5: Install SSL certificates (free via Let's Encrypt)

```bash
certbot --nginx -d tradexy.site -d www.tradexy.site -d dev.tradexy.site -d staging.tradexy.site
```

Follow the prompts:
- Enter your email
- Agree to terms
- Choose **"2: Redirect HTTP to HTTPS"** when asked

> Certbot **auto-renews** — no maintenance needed. Test renewal with:
> ```bash
> certbot renew --dry-run
> ```

### Step 6: Update `.env` APP_URL on server

```bash
# Already set in Step 5 "Server Setup" above, but verify:
# /var/www/tradexy-dev/.env    → APP_URL=https://dev.tradexy.site
# /var/www/tradexy-staging/.env → APP_URL=https://staging.tradexy.site
# /var/www/tradexy-prod/.env   → APP_URL=https://tradexy.site
```

### Step 7: Deploy each environment via CI/CD

> ⚠️ **DO NOT run `docker-compose up` manually before deploying via CI/CD.** The Docker images (`:dev`, `:staging`, `:main`) only exist on GHCR after CI/CD builds and pushes them. Running `docker-compose up` before that will fail with "image not found" and cause 502 errors.

Deploy each environment by pushing to the corresponding branch from your **local machine**:

```bash
# 1. Deploy dev first
git checkout dev
git push origin dev
# Wait for CI/CD to finish (check GitHub Actions tab)

# 2. Then staging
git checkout staging
git merge dev
git push origin staging
# Wait for CI/CD to finish

# 3. Then production
git checkout main
git merge staging
git push origin main
# Wait for CI/CD to finish
```

Each CI/CD run will: build image → push to GHCR → SCP compose files to server → pull image → start containers.

### Step 8: Verify

After all 3 pipelines have completed, visit:
- ✅ `https://dev.tradexy.site` → Dev
- ✅ `https://staging.tradexy.site` → Staging
- ✅ `https://tradexy.site` → Production

All should show a 🔒 lock icon (SSL active).

> **If you get a 502 after CI/CD succeeds**, SSH in and restart the containers:
> ```bash
> cd /var/www/tradexy-<env>
> docker-compose -f docker-compose.<env>.yml down
> docker-compose -f docker-compose.<env>.yml up -d
> ```

---

## 8. Deploying <a id="deploying"></a>

Once the server setup above is done, deployments are **fully automatic**:

### Deploy to Dev
```bash
git checkout dev
# make your changes, commit...
git push origin dev
```
CI/CD will: run tests → build image → push to GHCR → SCP compose+nginx files to server → SSH pull & restart.

### Deploy to Staging
```bash
git checkout staging
git merge dev
git push origin staging
```

### Deploy to Production
```bash
git checkout main
git merge staging
git push origin main
```

### Monitor
Go to GitHub repo → **Actions** tab → watch the pipeline.

### Access in browser

| Environment | URL |
|------------|-----|
| Dev | `https://dev.tradexy.site` |
| Staging | `https://staging.tradexy.site` |
| Production | `https://tradexy.site` |

---

## 9. Verifying a Deployment <a id="verifying"></a>

SSH into the server and run:

```bash
# Dev
cd /var/www/tradexy-dev
docker-compose -f docker-compose.dev.yml ps
docker-compose -f docker-compose.dev.yml exec app php artisan tinker --execute="echo config('app.key');"
docker-compose -f docker-compose.dev.yml logs app
docker-compose -f docker-compose.dev.yml logs nginx

# Staging
cd /var/www/tradexy-staging
docker-compose -f docker-compose.staging.yml ps

# Production
cd /var/www/tradexy-prod
docker-compose -f docker-compose.prod.yml ps
```

---

## 10. Manual Deploy (deploy.sh) <a id="manual-deploy"></a>

If CI/CD fails or you need to deploy manually from the server:

```bash
# First, copy deploy.sh to the server (from your local machine)
scp -i ~/.ssh/<YOUR_KEY> deploy.sh root@<YOUR_SERVER_IP>:/var/www/

# SSH in
ssh -i ~/.ssh/<YOUR_KEY> root@<YOUR_SERVER_IP>

# Make it executable
chmod +x /var/www/deploy.sh

# Deploy
cd /var/www
./deploy.sh dev
./deploy.sh staging
./deploy.sh production
```

---

## 11. Disk Cleanup <a id="disk-cleanup"></a>

### Remove old git clone data (one-time)

```bash
# Check what's using disk space
du -sh /var/www/tradexy-dev/*
du -sh /var/www/tradexy-staging/*
du -sh /var/www/tradexy-prod/*

# Remove old source code (keep .env and docker configs!)
cd /var/www/tradexy-dev
rm -rf .git vendor node_modules app config database public resources routes storage tests bootstrap artisan composer.* package.* webpack.* vite.*
```

### Docker cleanup

```bash
docker system df              # Check Docker disk usage
docker image prune -af        # Remove unused images
docker volume prune -f        # Remove unused volumes
docker system prune -af       # Remove everything unused
```

---

## 12. Troubleshooting <a id="troubleshooting"></a>

| Problem | Solution |
|---------|----------|
| `APP_KEY` error | Check `.env` exists in the server dir and has `APP_KEY=base64:...` |
| `Bad credentials` on build | Repo → Settings → Actions → General → Set workflow permissions to "Read and write" |
| `unknown shorthand flag: 'f'` | Server has Docker Compose V1 — use `docker-compose` (with hyphen) |
| Container won't start | `docker-compose -f docker-compose.<env>.yml logs app` |
| Can't connect to DB | Verify DB credentials in `.env`. Check Aiven/Supabase allows your server IP |
| Nginx 502 Bad Gateway | Containers not running. Check with `docker ps`, then: `cd /var/www/tradexy-<env> && docker-compose -f docker-compose.<env>.yml down && docker-compose -f docker-compose.<env>.yml up -d` |
| Permission errors | `docker-compose -f docker-compose.<env>.yml exec app chown -R www-data:www-data /var/www/storage /var/www/bootstrap/cache` |
| CI/CD fails on SCP | Ensure `SERVER_SSH_KEY` secret has the correct full private key |
| Image not found | Check GHCR: `https://github.com/codebykenth/trading-journal-v2/pkgs/container/trading-journal-v2` |
| SSL not working | Run `certbot --nginx -d tradexy.site -d www.tradexy.site -d dev.tradexy.site -d staging.tradexy.site` |
| `apt update` fails (MySQL GPG) | Run `rm -f /etc/apt/sources.list.d/mysql*.list` then retry |
| DNS not resolving | Wait 5-30 min for propagation, verify with `ping tradexy.site` |
| Host Nginx config error | Run `nginx -t` to test config, `systemctl reload nginx` to apply |