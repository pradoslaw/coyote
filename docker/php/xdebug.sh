#!/bin/bash

XdebugFile='/usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini'

if [[ "$ENABLE_XDEBUG" == "1" ]]; then
    if [ -f $XdebugFile ]; then
        echo "Xdebug enabled"
    else
        echo "Enabling xdebug"

        docker-php-ext-enable xdebug

        if [ -f $XdebugFile ]; then
            # See if file contains xdebug text.
            if grep -q xdebug.remote_enable "$XdebugFile"; then
                echo "Xdebug already enabled... skipping"
            else
                echo "zend_extension=$(find /usr/local/lib/php/extensions/ -name xdebug.so)" >$XdebugFile # Note, single arrow to overwrite file.
                echo "xdebug.remote_enable=1 " >>$XdebugFile
                echo "xdebug.remote_host=${XDEBUG_HOST}" >>$XdebugFile
                echo "xdebug.remote_port=${XDEBUG_PORT}" >>$XdebugFile
                echo "xdebug.profiler_enable_trigger=1" >>$XdebugFile
                echo "xdebug.remote_autostart=false " >>$XdebugFile
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
