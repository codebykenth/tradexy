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
        ;;
    staging)
        COMPOSE_FILE="docker-compose.staging.yml"
        ;;
    production)
        COMPOSE_FILE="docker-compose.prod.yml"
        ;;
    *)
        echo "❌ Invalid environment: $ENV"
        echo "Usage: ./deploy.sh [dev|staging|production]"
        exit 1
        ;;
esac

echo "📦 Step 1: Pulling latest code..."
git pull origin $(git branch --show-current)

echo "🛑 Step 2: Stopping old containers..."
docker-compose -f $COMPOSE_FILE down

echo "🔨 Step 3: Building new containers..."
docker-compose -f $COMPOSE_FILE build --no-cache

echo "🚀 Step 4: Starting containers..."
docker-compose -f $COMPOSE_FILE up -d

echo "⏳ Step 5: Waiting for containers to be ready..."
sleep 15

echo "🔑 Step 6: Generating app key..."
docker-compose -f $COMPOSE_FILE exec -T app php artisan key:generate --force || true

echo "📂 Step 7: Running migrations..."
docker-compose -f $COMPOSE_FILE exec -T app php artisan migrate --force

echo "🔗 Step 8: Creating storage link..."
docker-compose -f $COMPOSE_FILE exec -T app php artisan storage:link || true

if [ "$ENV" != "dev" ]; then
    echo "⚡ Step 9: Optimizing for $ENV..."
    docker-compose -f $COMPOSE_FILE exec -T app php artisan config:cache
    docker-compose -f $COMPOSE_FILE exec -T app php artisan route:cache
    docker-compose -f $COMPOSE_FILE exec -T app php artisan view:cache
else
    echo "⚡ Step 9: Clearing cache for dev..."
    docker-compose -f $COMPOSE_FILE exec -T app php artisan config:clear
    docker-compose -f $COMPOSE_FILE exec -T app php artisan route:clear
    docker-compose -f $COMPOSE_FILE exec -T app php artisan view:clear
fi

echo "🔒 Step 10: Setting permissions..."
docker-compose -f $COMPOSE_FILE exec -T app chown -R www-data:www-data /var/www/storage /var/www/bootstrap/cache

echo "========================================="
echo "✅ Deployment to $ENV complete!"
echo "========================================="
echo ""
echo "Running containers:"
docker ps --filter "name=tradexy" --format "table {{.Names}}\t{{.Status}}\t{{.Ports}}"