#!/bin/sh
set -e
echo "deploying application ..."
#php artisan down
git pull origin Development
# Install dependencies based on lock file
composer install --no-interaction --prefer-dist --optimize-autoloader --ignore-platform-reqs
# Migrate database
php artisan migrate
# Note: If you're using queue workers, this is the place to restart them.
# Clear cache
php artisan cache:clear
php artisan optimize:clear
# Reload PHP to update opcache echo ""
sudo -S service php8.2-fpm reload
#Install frontend dependecies npm install
#build frontend in production npm run production
# Exit maintenance mode
# php artisan up
echo "Application Deployed"
