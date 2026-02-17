# Deployment Guide — Tradexy

## Architecture Overview

```
GitHub (push) → CI/CD builds image → pushes to GHCR → SSHs to VPS → pulls image → runs compose
```

| Branch | GHCR Tag | Server Dir | Port | Compose File |
|--------|----------|------------|------|-------------|
| `dev` | `:dev` | `/var/www/tradexy-dev` | 8080 | `docker-compose.dev.yml` |
| `staging` | `:staging` | `/var/www/tradexy-staging` | 8081 | `docker-compose.staging.yml` |
| `main` | `:main` | `/var/www/tradexy-prod` | 8082 | `docker-compose.prod.yml` |

---

## One-Time Server Setup (Per Environment)

Do this **once** for each environment. Example for **dev** — repeat for staging/prod with the appropriate directory.

### Step 1: Create the directory

```bash
sudo mkdir -p /var/www/tradexy-dev/docker/nginx
```

### Step 2: Create the `.env` file

```bash
sudo nano /var/www/tradexy-dev/.env
```

Paste your Laravel environment config. The critical keys:

```env
APP_NAME=Tradexy
APP_ENV=local
APP_KEY=base64:YOUR_APP_KEY_HERE
APP_DEBUG=true
APP_URL=http://your-dev-domain.com

DB_CONNECTION=pgsql
DB_HOST=your-aiven-host.aivencloud.com
DB_PORT=5432
DB_DATABASE=your_db_name
DB_USERNAME=your_username
DB_PASSWORD=your_password

# ... rest of your config
```

> **How to generate APP_KEY:** Run this locally:
> ```bash
> php artisan key:generate --show
> ```
> Copy the output (starts with `base64:`) into your `.env` file.

### Step 3: Set permissions

```bash
sudo chown -R $USER:$USER /var/www/tradexy-dev
```

### Repeat for staging and prod

```bash
# Staging
sudo mkdir -p /var/www/tradexy-staging/docker/nginx
sudo nano /var/www/tradexy-staging/.env   # Use Supabase DB credentials, APP_ENV=staging

# Production
sudo mkdir -p /var/www/tradexy-prod/docker/nginx
sudo nano /var/www/tradexy-prod/.env      # Use Supabase DB credentials, APP_ENV=production
```

---

## Deploying (Automatic via CI/CD)

Once the server setup above is done, deployments are **fully automatic**:

### Deploy to Dev
```bash
git checkout dev
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

### Monitor deployment
Go to your GitHub repo → **Actions** tab → watch the pipeline run.

---

## Verifying a Deployment

SSH into your VPS and run:

```bash
# Check containers are running
cd /var/www/tradexy-dev
docker compose -f docker-compose.dev.yml ps

# Check APP_KEY is loaded
docker compose -f docker-compose.dev.yml exec app php artisan tinker --execute="echo config('app.key');"

# Check logs if something is wrong
docker compose -f docker-compose.dev.yml logs app
docker compose -f docker-compose.dev.yml logs nginx
```

---

## Cleaning Up Disk Space

### Remove old git clone data (one-time, after verifying new deploy works)

```bash
# Check what's using disk space
du -sh /var/www/tradexy-dev/*
du -sh /var/www/tradexy-staging/*
du -sh /var/www/tradexy-prod/*

# Remove old git repo files (NOT .env or docker configs!)
cd /var/www/tradexy-dev
rm -rf .git vendor node_modules app config database public resources routes storage tests bootstrap artisan composer.* package.* webpack.* vite.*

# Repeat for staging/prod if they have old clones
```

### Check Docker disk usage

```bash
docker system df           # Overview
docker image prune -af     # Remove all unused images
docker volume prune -f     # Remove unused volumes (careful: this deletes data!)
docker system prune -af    # Nuclear option: clean everything unused
```

---

## Troubleshooting

| Problem | Solution |
|---------|----------|
| `APP_KEY` error | Check `.env` exists in the server dir and has a valid `APP_KEY=base64:...` |
| Container won't start | Run `docker compose -f docker-compose.<env>.yml logs app` |
| Can't connect to DB | Verify `DB_HOST`, `DB_PORT`, `DB_USERNAME`, `DB_PASSWORD` in `.env` |
| Nginx 502 Bad Gateway | App container crashed — check app logs |
| Permission errors | Run `docker compose -f docker-compose.<env>.yml exec app chown -R www-data:www-data /var/www/storage /var/www/bootstrap/cache` |
| CI/CD fails on SCP | Ensure `SERVER_SSH_KEY` secret has the correct private key |