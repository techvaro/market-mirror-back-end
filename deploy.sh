#!/usr/bin/env bash
set -e
# Run this on the production server at /var/www/themarketmirror.com (or equivalent)
cd /var/www/themarketmirror.com
git pull origin main 2>/dev/null || git pull origin master 2>/dev/null || true
composer install --no-dev --optimize-autoloader
php artisan config:clear
php artisan route:clear
php artisan view:clear
php artisan cache:clear
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan storage:link 2>/dev/null || true
php artisan migrate --force
echo "Done"
