#!/bin/bash

set -e

ENV=$1

if [ -z "$ENV" ]; then
    echo "❌ Usage: ./deploy.sh [dev|staging|production]"
    exit 1
fi

echo "========================================="
echo "🚀 Deploying Tradexy to $ENV"
echo "========================================="

case $ENV in
    dev)
        PROJECT_DIR="/var/www/tradexy-dev"
        IMAGE_TAG="dev"
        ;;
    staging)
        PROJECT_DIR="/var/www/tradexy-staging"
        IMAGE_TAG="staging"
        ;;
    production)
        PROJECT_DIR="/var/www/tradexy-prod"
        IMAGE_TAG="main"
        ;;
    *)
        echo "❌ Invalid environment: $ENV"
        echo "Usage: ./deploy.sh [dev|staging|production]"
        exit 1
        ;;
esac

cd $PROJECT_DIR

echo "📄 Step 1: Checking .env file..."
if [ ! -f .env ]; then
    echo "  ❌ No .env file found in $PROJECT_DIR"
    exit 1
fi
echo "  ✅ .env exists"

echo "🔑 Step 2: Ensuring APP_KEY..."
APP_KEY=$(grep "^APP_KEY=" .env | cut -d '=' -f2)
if [ -z "$APP_KEY" ] || [ "$APP_KEY" = "" ]; then
    NEW_KEY="base64:$(openssl rand -base64 32)"
    sed -i "s|^APP_KEY=.*|APP_KEY=$NEW_KEY|" .env
    echo "  ✅ App key generated"
else
    echo "  ✅ App key exists"
fi

echo "🐳 Step 3: Pulling latest image..."
docker pull ghcr.io/YOUR_GITHUB_USERNAME/YOUR_REPO_NAME:$IMAGE_TAG

echo "🛑 Step 4: Stopping old containers..."
docker compose down

echo "🚀 Step 5: Starting containers..."
docker compose up -d

echo "⏳ Step 6: Waiting for health check..."
sleep 15

echo "📂 Step 7: Running migrations..."
docker compose exec -T app php artisan migrate --force

echo "🔗 Step 8: Creating storage link..."
docker compose exec -T app php artisan storage:link || true

if [ "$ENV" != "dev" ]; then
    echo "⚡ Step 9: Caching for $ENV..."
    docker compose exec -T app php artisan config:cache
    docker compose exec -T app php artisan route:cache
    docker compose exec -T app php artisan view:cache
else
    echo "⚡ Step 9: Clearing cache for dev..."
    docker compose exec -T app php artisan config:clear
    docker compose exec -T app php artisan route:clear
    docker compose exec -T app php artisan view:clear
fi

echo "🔒 Step 10: Setting permissions..."
docker compose exec -T app chown -R www-data:www-data /var/www/storage /var/www/bootstrap/cache

echo "🧹 Step 11: Cleaning old images..."
docker image prune -a --force --filter "until=168h"

echo "========================================="
echo "✅ Deployment to $ENV complete!"
echo "========================================="
echo ""
docker ps --filter "name=tradexy" --format "table {{.Names}}\t{{.Status}}\t{{.Ports}}"