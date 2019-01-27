FROM php:7.2-fpm

LABEL maintainer="4programmers.net <support@4programmers.net>"

RUN usermod -u 1000 www-data

RUN apt-get update -yqq && apt-get install -y libmcrypt-dev libpq-dev locales zlib1g-dev libfreetype6-dev libpng-dev gnupg \
    && apt-get -y install libxml2-dev \
    && docker-php-ext-configure gd --with-freetype-dir=/usr/include/ --with-png-dir=/usr/include/ \
    && docker-php-ext-install -j$(nproc) pgsql \
    && docker-php-ext-install gd soap pdo_pgsql mbstring zip

RUN locale-gen en_US.utf8 pl_PL.utf8 de_DE.utf8
RUN curl --insecure https://getcomposer.org/composer.phar -o /usr/bin/composer && chmod +x /usr/bin/composer

RUN echo Europe/Warsaw >/etc/timezone && \
    ln -sf /usr/share/zoneinfo/Europe/Warsaw /etc/localtime && \
    dpkg-reconfigure -f noninteractive tzdata

RUN curl -sL https://deb.nodesource.com/setup_8.x | bash -
RUN curl -sS http://dl.yarnpkg.com/debian/pubkey.gpg | apt-key add -

RUN echo "deb http://dl.yarnpkg.com/debian/ stable main" | tee /etc/apt/sources.list.d/yarn.list

RUN apt-get update && apt-get install -qq -y nodejs yarn && apt-get remove --purge --auto-remove -y
