#!/bin/sh
set -e

# Sync public files to the shared volume so Nginx can serve them.
# This runs on every container start to ensure assets stay up-to-date
# after each deployment.
echo "Syncing public files to shared volume..."
cp -r /var/www/public/. /var/www/public-shared/

# If a custom command was passed (e.g. scheduler, queue worker),
# execute it instead of the default PHP-FPM process.
if [ $# -gt 0 ]; then
    echo "Running custom command: $@"
    exec "$@"
fi

echo "Starting PHP-FPM..."
exec php-fpm
