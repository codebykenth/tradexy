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
        ;;
    staging)
        COMPOSE_FILE="docker-compose.staging.yml"
        PROJECT_DIR="/var/www/tradexy-staging"
        BRANCH="staging"
        ;;
    production)
        COMPOSE_FILE="docker-compose.prod.yml"
        PROJECT_DIR="/var/www/tradexy-prod"
        BRANCH="main"
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

echo "📄 Step 2: Checking .env file..."
if [ ! -f .env ]; then
    echo "  ❌ No .env file found in $PROJECT_DIR"
    echo "  Create one with: nano $PROJECT_DIR/.env"
    exit 1
fi

echo "🔑 Step 3: Ensuring APP_KEY exists BEFORE build..."
APP_KEY=$(grep "^APP_KEY=" .env | cut -d '=' -f2)
if [ -z "$APP_KEY" ] || [ "$APP_KEY" = "" ]; then
    echo "  🔑 No key found, generating one..."
    # Generate a key manually (base64 random)
    NEW_KEY="base64:$(openssl rand -base64 32)"
    # Write it to .env on the HOST
    sed -i "s|^APP_KEY=.*|APP_KEY=$NEW_KEY|" .env
    echo "  ✅ App key set: ${NEW_KEY:0:20}..."
else
    echo "  ✅ App key already exists: ${APP_KEY:0:20}..."
fi

echo "🛑 Step 4: Stopping old containers..."
docker-compose -f $COMPOSE_FILE down

echo "🔨 Step 5: Building new containers..."
docker-compose -f $COMPOSE_FILE build --no-cache

echo "🚀 Step 6: Starting containers..."
docker-compose -f $COMPOSE_FILE up -d

echo "⏳ Step 7: Waiting for containers to be ready..."
sleep 10

echo "📦 Step 8: Installing dependencies..."
if [ "$ENV" = "dev" ]; then
    docker-compose -f $COMPOSE_FILE exec -T app composer install --no-interaction --prefer-dist
else
    docker-compose -f $COMPOSE_FILE exec -T app composer install --no-dev --optimize-autoloader --no-interaction --prefer-dist
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