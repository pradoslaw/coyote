FROM php:7.3-fpm-alpine

LABEL maintainer="4programmers.net <support@4programmers.net>"

#RUN usermod -u 1000 www-data
WORKDIR /var/www

RUN apk update && apk add --no-cache \
    curl \
    bash \
    openssh-client \
    libmcrypt-dev \
    libpq \
    libzip-dev \
    freetype-dev \
    libcurl \
    libzip-dev \
    bzip2-dev \
    libjpeg-turbo-dev \
    postgresql-dev \
    freetype-dev \
    libpng-dev \
    gnupg \
    libxml2-dev \
    zip \
    make

RUN docker-php-ext-configure gd --with-freetype-dir=/usr/include/ --with-png-dir=/usr/include/ --with-jpeg-dir=/usr/include/ \
    && docker-php-ext-install -j$(nproc) pgsql gd soap pdo_pgsql mbstring zip opcache iconv

RUN curl --insecure https://getcomposer.org/composer.phar -o /usr/bin/composer && chmod +x /usr/bin/composer

RUN apk add tzdata
RUN cp /usr/share/zoneinfo/Europe/Brussels /etc/localtime
RUN echo "Europe/Warsaw" >  /etc/timezone

RUN apk add yarn
