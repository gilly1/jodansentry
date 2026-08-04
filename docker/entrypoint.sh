#!/bin/sh
set -e

# If running as the app container (supervisord), run Laravel setup
if [ "$1" = "supervisord" ]; then
    # Cache configuration
    php artisan config:cache
    php artisan route:cache
    php artisan view:cache

    # Link storage
    php artisan storage:link 2>/dev/null || true
fi

exec "$@"
