#!/bin/bash
set -e

echo "============================================"
echo "  🚀 Ketahanan Pangan — Container Init"
echo "============================================"

# ── 1. Create required storage directories ──────────────────
echo "📁 Creating storage directories..."
mkdir -p \
    /var/www/html/storage/logs \
    /var/www/html/storage/framework/sessions \
    /var/www/html/storage/framework/views \
    /var/www/html/storage/framework/cache/data \
    /var/www/html/storage/app/public \
    /var/www/html/bootstrap/cache

# ── 2. Fix permissions ─────────────────────────────────────
echo "🔒 Fixing permissions..."
chown -R www-data:www-data \
    /var/www/html/storage \
    /var/www/html/bootstrap/cache \
    /var/lib/nginx

chmod -R 775 \
    /var/www/html/storage \
    /var/www/html/bootstrap/cache

# ── 3. Create storage symlink ──────────────────────────────
if [ ! -L /var/www/html/public/storage ]; then
    echo "🔗 Creating storage symlink..."
    php artisan storage:link --no-ansi 2>/dev/null || echo "⚠️  storage:link skipped (may already exist)"
fi

# ── 4. Generate APP_KEY if missing ─────────────────────────
if [ -z "$APP_KEY" ] || [ "$APP_KEY" = "" ]; then
    echo "🔑 Generating APP_KEY..."
    php artisan key:generate --force --no-ansi
fi

# ── 5. Determine container role ────────────────────────────
ROLE=${CONTAINER_ROLE:-app}
echo "📌 Container role: $ROLE"

case "$ROLE" in
    app)
        # ── Run migrations if enabled ──────────────────────
        if [ "${RUN_MIGRATIONS:-false}" = "true" ]; then
            echo "🗃️  Running migrations..."
            php artisan migrate --force --no-ansi 2>/dev/null || echo "⚠️  migrate skipped (may need manual run)"
        fi

        # ── Run seeders if enabled ─────────────────────────
        if [ "${RUN_SEEDERS:-false}" = "true" ]; then
            echo "🌱 Running seeders..."
            php artisan db:seed --force --no-ansi 2>/dev/null || echo "⚠️  seed skipped"
        fi

        # ── Laravel optimizations ──────────────────────────
        if [ "$APP_ENV" = "production" ]; then
            echo "⚙️  Running production optimizations..."
            php artisan config:cache --no-ansi 2>/dev/null || echo "⚠️  config:cache skipped"
            php artisan route:cache  --no-ansi 2>/dev/null || echo "⚠️  route:cache skipped"
            php artisan view:cache   --no-ansi 2>/dev/null || echo "⚠️  view:cache skipped"
            php artisan event:cache  --no-ansi 2>/dev/null || echo "⚠️  event:cache skipped"
        else
            # Development: clear all caches for fresh state
            echo "🧹 Clearing caches (development mode)..."
            php artisan config:clear --no-ansi 2>/dev/null || true
            php artisan route:clear  --no-ansi 2>/dev/null || true
            php artisan view:clear   --no-ansi 2>/dev/null || true
            php artisan cache:clear  --no-ansi 2>/dev/null || true
        fi

        echo ""
        echo "============================================"
        echo "  ✅ App ready — Starting Supervisor"
        echo "  🌐 URL: ${APP_URL:-http://localhost}"
        echo "============================================"

        # Hand off to supervisord (manages php-fpm + nginx)
        exec /usr/bin/supervisord -c /etc/supervisor/conf.d/supervisord.conf
        ;;

    queue)
        echo "✅ Starting queue worker..."
        exec php artisan queue:work \
            --sleep=3 \
            --tries=3 \
            --max-time=3600 \
            --no-ansi
        ;;

    scheduler)
        echo "✅ Starting scheduler..."
        while true; do
            php artisan schedule:run --no-ansi --verbose 2>&1
            sleep 60
        done
        ;;

    *)
        echo "❌ Unknown CONTAINER_ROLE: $ROLE"
        echo "   Valid roles: app, queue, scheduler"
        exit 1
        ;;
esac
