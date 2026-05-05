# ============================================================
# Stage 1: Node — build frontend assets (Vite/Tailwind)
# ============================================================
FROM node:20-alpine AS node_builder

WORKDIR /app

COPY package.json package-lock.json ./
RUN npm ci --prefer-offline

COPY resources/ resources/
COPY vite.config.js tailwind.config.js postcss.config.js ./
COPY public/ public/

RUN npm run build

# ============================================================
# Stage 2: PHP — production app image
# ============================================================
FROM php:8.2-fpm-alpine AS app

# System deps
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
    supervisor

# PHP extensions required by Laravel + maatwebsite/excel
RUN docker-php-ext-configure gd --with-freetype --with-jpeg \
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

# Allow composer to run as root inside Docker
ENV COMPOSER_ALLOW_SUPERUSER=1

# Composer
COPY --from=composer:2.7 /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html

# Copy composer files first (layer cache)
COPY composer.json composer.lock ./
RUN composer install --no-dev --no-scripts --no-autoloader --prefer-dist

# Copy full application source
COPY . .

# Copy built frontend assets from Stage 1
COPY --from=node_builder /app/public/build ./public/build

# Finish composer setup
# --no-scripts: skip artisan package:discover (dev packages like Breeze not installed)
# Discovery happens automatically when the app boots in production
RUN composer dump-autoload --optimize --no-dev --no-scripts

# Storage dirs are created at runtime by entrypoint.sh
# (named volumes would overwrite dirs created here at build time)

# Copy config files
COPY docker/nginx.conf /etc/nginx/nginx.conf
COPY docker/supervisord.conf /etc/supervisor/conf.d/supervisord.conf
COPY docker/php.ini /usr/local/etc/php/conf.d/custom.ini
COPY docker/entrypoint.sh /entrypoint.sh
RUN chmod +x /entrypoint.sh

EXPOSE 80

CMD ["/entrypoint.sh"]
