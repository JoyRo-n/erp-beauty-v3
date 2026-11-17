#!/usr/bin/env bash
set -e

cd /var/www/html

# Ensure .env exists
if [ ! -f .env ]; then
    cp .env.example .env
fi

# Ensure storage and cache directories exist and are writable
mkdir -p storage/framework/cache/data storage/framework/sessions storage/framework/views storage/logs bootstrap/cache database
chmod -R 775 storage bootstrap/cache database

# Generate APP_KEY if not already set
if ! grep -q "^APP_KEY=base64:" .env; then
    php artisan key:generate --force
fi

# Create fresh SQLite file and seed 100+ encrypted ERP records
touch database/database.sqlite
php artisan migrate --force
php artisan erp:seed-100
php artisan view:clear

PORT="${PORT:-8000}"
echo "Starting NusaGlow ERP Server on 0.0.0.0:${PORT}..."
exec php -S "0.0.0.0:${PORT}" -t public public/router.php
