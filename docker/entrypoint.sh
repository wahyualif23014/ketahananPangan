#!/bin/sh
set -e

echo "🚀 Initializing Laravel storage directories..."

# Create all required storage subdirectories
mkdir -p \
    /var/www/html/storage/logs \
    /var/www/html/storage/framework/sessions \
    /var/www/html/storage/framework/views \
    /var/www/html/storage/framework/cache/data \
    /var/www/html/storage/app/public \
    /var/www/html/bootstrap/cache

# Fix ownership and permissions
chown -R www-data:www-data \
    /var/www/html/storage \
    /var/www/html/bootstrap/cache

chmod -R 775 \
    /var/www/html/storage \
    /var/www/html/bootstrap/cache

echo "✅ Storage directories ready."

# Run Laravel optimizations
echo "⚙️  Running Laravel optimizations..."
php artisan config:cache   --no-ansi 2>/dev/null || echo "⚠️  config:cache skipped"
php artisan route:cache    --no-ansi 2>/dev/null || echo "⚠️  route:cache skipped"
php artisan view:cache     --no-ansi 2>/dev/null || echo "⚠️  view:cache skipped"

echo "✅ Ready! Starting services..."

# Hand off to supervisord
exec /usr/bin/supervisord -c /etc/supervisor/conf.d/supervisord.conf
