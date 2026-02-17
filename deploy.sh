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
        COMPOSE_FILE="docker-compose.dev.yml"
        PROJECT_DIR="/var/www/tradexy-dev"
        BRANCH="dev"
        ENV_FILE=".env"
        ;;
    staging)
        COMPOSE_FILE="docker-compose.staging.yml"
        PROJECT_DIR="/var/www/tradexy-staging"
        BRANCH="staging"
        ENV_FILE=".env.staging"
        ;;
    production)
        COMPOSE_FILE="docker-compose.prod.yml"
        PROJECT_DIR="/var/www/tradexy-prod"
        BRANCH="main"
        ENV_FILE=".env.production"
        ;;
    *)
        echo "❌ Invalid environment: $ENV"
        echo "Usage: ./deploy.sh [dev|staging|production]"
        exit 1
        ;;
esac

cd $PROJECT_DIR

echo "📦 Step 1: Pulling latest code..."
git fetch origin
git checkout $BRANCH
git pull origin $BRANCH

echo "📄 Step 2: Ensuring .env file exists..."
if [ ! -f .env ]; then
    if [ -f "$ENV_FILE" ] && [ "$ENV_FILE" != ".env" ]; then
        echo "  📄 Copying $ENV_FILE to .env"
        cp $ENV_FILE .env
    elif [ -f .env.example ]; then
        echo "  📄 Copying .env.example to .env"
        cp .env.example .env
    else
        echo "  ❌ No .env file found! Create one manually."
        exit 1
    fi
fi

echo "🛑 Step 3: Stopping old containers..."
docker-compose -f $COMPOSE_FILE down

echo "🔨 Step 4: Building new containers..."
docker-compose -f $COMPOSE_FILE build --no-cache

echo "🚀 Step 5: Starting containers..."
docker-compose -f $COMPOSE_FILE up -d

echo "⏳ Step 6: Waiting for containers to be ready..."
sleep 10

echo "📦 Step 7: Installing dependencies..."
docker-compose -f $COMPOSE_FILE exec -T app composer install --no-interaction --prefer-dist

echo "🔑 Step 8: Checking app key..."
APP_KEY=$(grep "^APP_KEY=" .env | cut -d '=' -f2)
if [ -z "$APP_KEY" ] || [ "$APP_KEY" = "" ]; then
    echo "  🔑 No key found, generating..."
    docker-compose -f $COMPOSE_FILE exec -T app php artisan key:generate --force
    echo "  ✅ App key generated."
else
    echo "  ✅ App key already exists, skipping."
fi

echo "📂 Step 9: Running migrations..."
docker-compose -f $COMPOSE_FILE exec -T app php artisan migrate --force

echo "🔗 Step 10: Creating storage link..."
docker-compose -f $COMPOSE_FILE exec -T app php artisan storage:link || true

if [ "$ENV" != "dev" ]; then
    echo "⚡ Step 11: Optimizing for $ENV..."
    docker-compose -f $COMPOSE_FILE exec -T app php artisan config:cache
    docker-compose -f $COMPOSE_FILE exec -T app php artisan route:cache
    docker-compose -f $COMPOSE_FILE exec -T app php artisan view:cache
else
    echo "⚡ Step 11: Clearing cache for dev..."
    docker-compose -f $COMPOSE_FILE exec -T app php artisan config:clear
    docker-compose -f $COMPOSE_FILE exec -T app php artisan route:clear
    docker-compose -f $COMPOSE_FILE exec -T app php artisan view:clear
fi

echo "🔒 Step 12: Setting permissions..."
docker-compose -f $COMPOSE_FILE exec -T app chown -R www-data:www-data /var/www/storage /var/www/bootstrap/cache

echo "========================================="
echo "✅ Deployment to $ENV complete!"
echo "========================================="
echo ""
echo "Running containers:"
docker ps --filter "name=tradexy" --format "table {{.Names}}\t{{.Status}}\t{{.Ports}}"