#!/bin/sh
set -e

cd /var/www/html

# ── Ensure storage directories are writable ───────────────────
chown -R www-data:www-data storage bootstrap/cache
chmod -R 775 storage bootstrap/cache

# ── Generate APP_KEY if not set ───────────────────────────────
if [ ! -f .env ]; then
    if [ -f .env.example ]; then
        cp .env.example .env
    else
        cat > .env <<'EOF'
APP_NAME=${APP_NAME:-MpesaSalary}
APP_ENV=${APP_ENV:-production}
APP_KEY=
APP_DEBUG=${APP_DEBUG:-false}
APP_URL=${APP_URL:-http://localhost}
DB_CONNECTION=mysql
EOF
    fi
    chown www-data:www-data .env
    chmod 664 .env
fi

# Generate APP_KEY if not provided via environment and not present in .env
if [ -z "$APP_KEY" ]; then
    current_key=$(grep -E '^APP_KEY=' .env | cut -d '=' -f2- | tr -d '\r' || true)
    if [ -z "$current_key" ]; then
        php artisan key:generate --force --no-interaction
        APP_KEY=$(grep -E '^APP_KEY=' .env | cut -d '=' -f2- | tr -d '\r' || true)
        export APP_KEY
    fi
fi

# ── Wait for database to be ready ─────────────────────────────
echo "Waiting for database..."
max_tries=30
count=0
until php artisan db:monitor --databases=mysql 2>/dev/null || [ $count -ge $max_tries ]; do
    count=$((count + 1))
    echo "Database not ready (attempt $count/$max_tries)..."
    sleep 2
done

# ── Run migrations ────────────────────────────────────────────
php artisan migrate --force --no-interaction

# ── Cache config, routes, views for production ────────────────
if [ "$APP_ENV" = "production" ]; then
    php artisan config:cache
    php artisan route:cache
    php artisan view:cache
fi

# ── Link public storage ──────────────────────────────────────
php artisan storage:link --force 2>/dev/null || true

exec "$@"
