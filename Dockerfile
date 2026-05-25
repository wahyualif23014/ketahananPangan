# ============================================================
# Stage 1: Node — build frontend assets (Vite + TailwindCSS)
# ============================================================
FROM node:20-alpine AS node_builder

WORKDIR /build

# Layer cache: install deps first
COPY package.json package-lock.json ./
RUN npm ci --prefer-offline

# Copy frontend source + config files
COPY resources/ resources/
COPY vite.config.js tailwind.config.js postcss.config.js ./
COPY public/ public/

# Build production assets
RUN npm run build


# ============================================================
# Stage 2: PHP Application (PHP-FPM + Nginx + Supervisor)
# ============================================================
FROM php:8.2-fpm-alpine AS app

LABEL maintainer="Ketahanan Pangan Team"
LABEL description="Laravel Ketahanan Pangan Application"

# ── System dependencies ──────────────────────────────────────
RUN apk add --no-cache \
    bash \
    curl \
    libzip-dev \
    zip \
    unzip \
    libpng-dev \
    libjpeg-turbo-dev \
    freetype-dev \
    icu-dev \
    oniguruma-dev \
    libxml2-dev \
    shadow \
    nginx \
    supervisor \
    # For healthcheck
    fcgi

# ── PHP extensions (Laravel + maatwebsite/excel) ─────────────
RUN docker-php-ext-configure gd \
        --with-freetype \
        --with-jpeg \
    && docker-php-ext-install -j$(nproc) \
        pdo_mysql \
        mbstring \
        zip \
        exif \
        pcntl \
        bcmath \
        gd \
        intl \
        xml \
        opcache

# ── Composer ────────────────────────────────────────────────
ENV COMPOSER_ALLOW_SUPERUSER=1
COPY --from=composer:2.8 /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html

# ── Layer cache: Composer deps ──────────────────────────────
COPY composer.json composer.lock ./
RUN composer install \
    --no-dev \
    --no-scripts \
    --no-autoloader \
    --prefer-dist \
    --no-interaction

# ── Copy full application source ────────────────────────────
COPY . .

# ── Copy built frontend assets from Stage 1 ─────────────────
COPY --from=node_builder /build/public/build ./public/build

# ── Finish Composer autoload ────────────────────────────────
RUN composer dump-autoload --optimize --no-dev --classmap-authoritative

# ── Create storage directory structure ──────────────────────
RUN mkdir -p \
    storage/logs \
    storage/framework/sessions \
    storage/framework/views \
    storage/framework/cache/data \
    storage/app/public \
    bootstrap/cache

# ── Set ownership to www-data ───────────────────────────────
RUN chown -R www-data:www-data /var/www/html \
    && chmod -R 775 storage bootstrap/cache

# ── Copy Docker config files ───────────────────────────────
COPY docker/nginx.conf        /etc/nginx/nginx.conf
COPY docker/supervisord.conf  /etc/supervisor/conf.d/supervisord.conf
COPY docker/php.ini           /usr/local/etc/php/conf.d/99-custom.ini
COPY docker/entrypoint.sh     /entrypoint.sh
RUN chmod +x /entrypoint.sh

# ── Healthcheck ─────────────────────────────────────────────
HEALTHCHECK --interval=30s --timeout=5s --start-period=30s --retries=3 \
    CMD curl -f http://localhost/up 2>/dev/null || curl -f http://localhost/ 2>/dev/null || exit 1

EXPOSE 80

ENTRYPOINT ["/entrypoint.sh"]
