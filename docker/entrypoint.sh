#!/usr/bin/env sh
set -e

cd /var/www/html

# Ensure correct permissions for storage and cache
chown -R www-data:www-data storage bootstrap/cache || true
chmod -R 775 storage bootstrap/cache || true

# If vendor is empty (named volume), install dependencies
if [ ! -f vendor/autoload.php ]; then
  echo "[entrypoint] Installing composer dependencies..."
  composer install --no-interaction --no-ansi --no-progress --prefer-dist --no-dev
fi

# Generate app key if missing
if ! grep -q "^APP_KEY=base64" .env 2>/dev/null; then
  echo "[entrypoint] Generating APP_KEY..."
  php artisan key:generate --force || true
fi

# Create storage symlink
php artisan storage:link || true

# Run migrations (optional; disable by setting SKIP_MIGRATIONS=1)
if [ "${SKIP_MIGRATIONS}" != "1" ]; then
  echo "[entrypoint] Running migrations..."
  php artisan migrate --force || true
fi

# Optimize caches
php artisan config:cache || true
php artisan route:cache || true
php artisan view:cache || true

# Start PHP-FPM
exec php-fpm
