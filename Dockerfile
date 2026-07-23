# ─────────────────────────────────────────────────────────────
# Stage 1: Build frontend assets (Tailwind CSS via Vite)
# ─────────────────────────────────────────────────────────────
FROM node:22-alpine AS frontend

WORKDIR /app

COPY package.json package-lock.json* ./
RUN npm ci --ignore-scripts

COPY vite.config.js ./
COPY resources/ resources/
COPY public/ public/

RUN npm run build

# ─────────────────────────────────────────────────────────────
# Stage 2: Install PHP dependencies
# ─────────────────────────────────────────────────────────────
FROM composer:2 AS vendor

WORKDIR /app

COPY composer.json composer.lock ./
RUN composer install \
    --no-dev \
    --no-interaction \
    --no-scripts \
    --prefer-dist \
    --optimize-autoloader \
    --no-progress \
    --ignore-platform-reqs

# ─────────────────────────────────────────────────────────────
# Stage 3: Production image
# ─────────────────────────────────────────────────────────────
FROM php:8.3-fpm-alpine

# Install build deps (virtual package for easy cleanup) + runtime deps
RUN apk add --no-cache --virtual .build-deps \
        libzip-dev \
        icu-dev \
        oniguruma-dev \
        libpng-dev \
        libjpeg-turbo-dev \
        freetype-dev \
        linux-headers \
    && apk add --no-cache \
        nginx \
        supervisor \
        curl \
        zip \
        unzip \
        mysql-client \
        libzip \
        icu-libs \
        oniguruma \
        libpng \
        libjpeg-turbo \
        freetype \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install -j"$(nproc)" \
        pdo_mysql \
        zip \
        intl \
        mbstring \
        bcmath \
        gd \
        pcntl \
        opcache \
    && pecl install redis \
    && docker-php-ext-enable redis \
    && apk del .build-deps \
    && rm -rf /var/cache/apk/* /tmp/pear

# Configure PHP for production
RUN cp "$PHP_INI_DIR/php.ini-production" "$PHP_INI_DIR/php.ini" \
    && sed -i 's/upload_max_filesize = .*/upload_max_filesize = 64M/' "$PHP_INI_DIR/php.ini" \
    && sed -i 's/post_max_size = .*/post_max_size = 64M/' "$PHP_INI_DIR/php.ini" \
    && sed -i 's/memory_limit = .*/memory_limit = 512M/' "$PHP_INI_DIR/php.ini"

# Configure OPcache
RUN { \
    echo 'opcache.memory_consumption=256'; \
    echo 'opcache.interned_strings_buffer=16'; \
    echo 'opcache.max_accelerated_files=20000'; \
    echo 'opcache.revalidate_freq=0'; \
    echo 'opcache.validate_timestamps=0'; \
    echo 'opcache.enable_cli=1'; \
    } > "$PHP_INI_DIR/conf.d/opcache.ini"

WORKDIR /var/www/html

# Copy app source
COPY . .

# Copy built frontend assets from stage 1
COPY --from=frontend /app/public/build public/build

# Copy vendor from stage 2
COPY --from=vendor /app/vendor vendor/

# Nginx config
COPY docker/nginx.conf /etc/nginx/http.d/default.conf

# Supervisor config
COPY docker/supervisord.conf /etc/supervisor/conf.d/supervisord.conf

# Entrypoint
COPY docker/entrypoint.sh /usr/local/bin/entrypoint.sh
RUN chmod +x /usr/local/bin/entrypoint.sh

# Create required directories & set permissions
RUN mkdir -p \
        storage/app/public \
        storage/framework/cache/data \
        storage/framework/sessions \
        storage/framework/views \
        storage/logs \
        bootstrap/cache \
    && chown -R www-data:www-data storage bootstrap/cache \
    && chmod -R 775 storage bootstrap/cache

# Generate optimized autoload
RUN php artisan package:discover --ansi 2>/dev/null || true

EXPOSE 80

ENTRYPOINT ["entrypoint.sh"]
CMD ["supervisord", "-c", "/etc/supervisor/conf.d/supervisord.conf"]
