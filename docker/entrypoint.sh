#!/bin/sh
set -e

# Ensure storage directories exist
mkdir -p \
    storage/app/public \
    storage/framework/cache/data \
    storage/framework/sessions \
    storage/framework/views \
    storage/logs

# If running as the app container (supervisord), run Laravel setup
if [ "$1" = "supervisord" ]; then
    # Cache configuration
    php artisan config:cache
    php artisan route:cache
    php artisan view:cache

    # Link storage
    php artisan storage:link 2>/dev/null || true
fi

# Set permissions AFTER artisan commands to cover any files they created
chown -R www-data:www-data storage bootstrap/cache
chmod -R 775 storage bootstrap/cache

exec "$@"
