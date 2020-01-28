#!/bin/bash

if [ ! -z "$DB_MIGRATE" ]; then
    echo "Running database migration..."
    php artisan migrate --force

    cp -rp /app/. /var/www
    cd /var/www

    php artisan config:cache
    php artisan route:cache
fi

XdebugFile='/usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini'

if [[ "$ENABLE_XDEBUG" == "1" ]]; then
    if [ -f $XdebugFile ]; then
        echo "Xdebug enabled"
    else
        echo "Enabling xdebug"
        echo "If you get this error, you can safely ignore it: /usr/local/bin/docker-php-ext-enable: line 83: nm: not found"
        # see https://github.com/docker-library/php/pull/420
        docker-php-ext-enable xdebug
        # see if file exists
        if [ -f $XdebugFile ]; then
            # See if file contains xdebug text.
            if grep -q xdebug.remote_enable "$XdebugFile"; then
                echo "Xdebug already enabled... skipping"
            else
                echo "zend_extension=$(find /usr/local/lib/php/extensions/ -name xdebug.so)" >$XdebugFile # Note, single arrow to overwrite file.
                echo "xdebug.remote_enable=1 " >>$XdebugFile
                echo "xdebug.remote_host=host.docker.internal" >>$XdebugFile
                echo "xdebug.remote_log=/tmp/xdebug.log" >>$XdebugFile
                echo "xdebug.remote_autostart=false " >>$XdebugFile # I use the xdebug chrome extension instead of using autostart
                # NOTE: xdebug.remote_host is not needed here if you set an environment variable in docker-compose like so `- XDEBUG_CONFIG=remote_host=192.168.111.27`.
                #       you also need to set an env var `- PHP_IDE_CONFIG=serverName=docker`
            fi
        fi
    fi
else
    if [ -f $XdebugFile ]; then
        echo "Disabling Xdebug"
        rm $XdebugFile
    fi
fi

exec php-fpm
