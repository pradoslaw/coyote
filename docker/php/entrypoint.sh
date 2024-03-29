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
                echo "xdebug.mode=debug " >>$XdebugFile
                echo "xdebug.client_host=${XDEBUG_HOST}" >>$XdebugFile
                echo "xdebug.client_port=${XDEBUG_PORT}" >>$XdebugFile
                echo "xdebug.start_with_request=yes" >>$XdebugFile
                echo "xdebug.start_upon_error=yes" >>$XdebugFile
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
