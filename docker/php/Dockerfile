FROM php:7.4-fpm-alpine

LABEL maintainer="4programmers.net <support@4programmers.net>"

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
    libc-dev \
    linux-headers \
    zlib-dev \
    gnupg \
    libxml2-dev \
    zip \
    make \
    autoconf \
    gcc \
    git \
    oniguruma-dev

RUN docker-php-ext-configure gd --with-freetype=/usr/include/ --with-jpeg=/usr/include/ \
    && docker-php-ext-install -j$(nproc) pgsql gd soap pdo_pgsql mbstring zip opcache iconv

RUN pecl install xdebug

RUN curl --insecure https://getcomposer.org/composer.phar -o /usr/bin/composer && chmod +x /usr/bin/composer

RUN apk add tzdata
RUN cp /usr/share/zoneinfo/Europe/Warsaw /etc/localtime
RUN echo "Europe/Warsaw" >  /etc/timezone

RUN apk add yarn

ARG UID=1000
ARG GID=1000

ENV php_conf /usr/local/etc/php-fpm.conf
ENV fpm_conf /usr/local/etc/php-fpm.d/www.conf
ENV php_vars /usr/local/etc/php/conf.d/docker-vars.ini

RUN addgroup -g ${GID} -S nginx
RUN adduser -u ${UID} -D -S -h /var/cache/nginx -s /sbin/nologin -G nginx nginx

RUN echo "cgi.fix_pathinfo=0" > ${php_vars} &&\
    echo "upload_max_filesize = 100M"  >> ${php_vars} &&\
    echo "post_max_size = 100M"  >> ${php_vars} &&\
    echo "variables_order = \"EGPCS\""  >> ${php_vars} && \
    echo "memory_limit = 512M"  >> ${php_vars} && \
    sed -i \
        -e "s/;catch_workers_output\s*=\s*yes/catch_workers_output = yes/g" \
        -e "s/pm.max_children = 5/pm.max_children = 64/g" \
        -e "s/pm.start_servers = 2/pm.start_servers = 10/g" \
        -e "s/pm.min_spare_servers = 1/pm.min_spare_servers = 4/g" \
        -e "s/pm.max_spare_servers = 3/pm.max_spare_servers = 16/g" \
        -e "s/;pm.max_requests = 500/pm.max_requests = 500/g" \
        -e "s/user = www-data/user = nginx/g" \
        -e "s/group = www-data/group = nginx/g" \
        -e "s/;listen.mode = 0660/listen.mode = 0666/g" \
        -e "s/;listen.owner = www-data/listen.owner = nginx/g" \
        -e "s/;listen.group = www-data/listen.group = nginx/g" \
        -e "s/^;clear_env = no$/clear_env = no/" \
        ${fpm_conf}

RUN apk --no-cache add ca-certificates wget && \
    wget -q -O /etc/apk/keys/sgerrand.rsa.pub https://alpine-pkgs.sgerrand.com/sgerrand.rsa.pub && \
    wget https://github.com/sgerrand/alpine-pkg-glibc/releases/download/2.25-r0/glibc-2.25-r0.apk && \
    wget https://github.com/sgerrand/alpine-pkg-glibc/releases/download/2.25-r0/glibc-bin-2.25-r0.apk && \
    wget https://github.com/sgerrand/alpine-pkg-glibc/releases/download/2.25-r0/glibc-i18n-2.25-r0.apk && \
    apk add glibc-bin-2.25-r0.apk glibc-i18n-2.25-r0.apk glibc-2.25-r0.apk

RUN /usr/glibc-compat/bin/localedef -i pl_PL -f UTF-8 pl_PL.UTF-8

ENV LANG=pl_PL.UTF-8 \
    LANGUAGE=pl_PL.UTF-8
