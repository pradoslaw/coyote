#!/bin/bash

cp -rp /app/. /var/www

exec php-fpm
