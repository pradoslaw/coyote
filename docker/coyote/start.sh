#!/bin/bash

cp -rp /app/. /var/www
php artisan migrate
php artisan config:cache
php artisan route:cache

exec php-fpm
