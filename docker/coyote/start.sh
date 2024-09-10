#!/bin/bash

if [ ! -z "$DB_MIGRATE" ]; then
    php artisan migrate --force

    cp -rp /app/. /var/www
    cd /var/www

    php artisan config:cache
    php artisan route:cache
    php artisan twig:clean
fi

exec /entrypoint.sh
