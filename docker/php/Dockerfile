FROM php:7.0.8-fpm

LABEL maintainer="4programmers.net <support@4programmers.net>"

RUN apt-get update && \
    apt-get install -y libmcrypt-dev=2.5.8-3.3 libpq-dev=9.4.15-0+deb8u1 \
    mysql-client=5.5.60-0+deb8u1 libmagickwand-dev=8:6.8.9.9-5+deb8u12 --no-install-recommends \
    && apt-get remove --purge --auto-remove -y \
    && pecl install imagick \
    && docker-php-ext-enable imagick \
    && docker-php-ext-install mcrypt

RUN pecl install mongodb \
    && docker-php-ext-enable mongodb

RUN docker-php-ext-install pdo pdo_pgsql pdo_mysql gd mbstring mcrypt zip

RUN curl -sS http://dl.yarnpkg.com/debian/pubkey.gpg | apt-key add -
RUN echo "deb http://dl.yarnpkg.com/debian/ stable main" | tee /etc/apt/sources.list.d/yarn.list

RUN curl -sL https://deb.nodesource.com/setup_6.x | bash -
RUN apt-get update \
    && apt-get install -qq -y --no-install-recommends nodejs=6.14.2-1nodesource1 yarn=1.6.0-1 \
    && apt-get remove --purge --auto-remove -y

RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer
RUN composer --version
