#!/bin/bash

php artisan migrate

cp -rp /app/. /var/www
cd /var/www

php artisan config:cache
php artisan route:cache
composer dump-autoload

exec php-fpm
