FROM php:8.0.8-fpm-buster as backend

LABEL maintainer="4programmers.net <support@4programmers.net>"

WORKDIR /var/www

RUN apt-get update -yqq && apt-get install -y \
    gnupg \
    libmcrypt-dev \
    libpq-dev \
    locales \
    libpng-dev \
    libjpeg-dev \
    libwebp-dev \
    libfreetype6-dev \
    libzip-dev \
    zlib1g-dev \
    git \
    libgmp-dev \
    libxml2-dev \
    libcurl4-openssl-dev \
    pkg-config \
    libssl-dev \
    libonig-dev \
    cron \
    gnupg2

RUN docker-php-ext-configure gd --with-freetype=/usr/include/ --with-jpeg=/usr/include/ --with-webp \
    && docker-php-ext-install -j$(nproc) pgsql gd soap pdo_pgsql mbstring zip opcache iconv

RUN pecl install xdebug

RUN curl --insecure https://getcomposer.org/composer-stable.phar -o /usr/bin/composer && chmod +x /usr/bin/composer

RUN echo Europe/Warsaw >/etc/timezone && \
    ln -sf /usr/share/zoneinfo/Europe/Warsaw /etc/localtime && \
    dpkg-reconfigure -f noninteractive tzdata

ARG UID=1000
ARG GID=1000

ENV php_conf /usr/local/etc/php-fpm.conf
ENV fpm_conf /usr/local/etc/php-fpm.d/www.conf
ENV php_vars /usr/local/etc/php/conf.d/docker-vars.ini

RUN groupadd -g ${GID} nginx
RUN useradd -u ${UID} -g nginx nginx

RUN echo "cgi.fix_pathinfo=0" > ${php_vars} &&\
    echo "upload_max_filesize = 50M"  >> ${php_vars} &&\
    echo "post_max_size = 50M"  >> ${php_vars} &&\
    echo "variables_order = \"EGPCS\""  >> ${php_vars} && \
    echo "memory_limit = 512M"  >> ${php_vars} && \
    echo "opcache.preload = /var/www/preload.php" >> ${php_vars} && \
    echo "opcache.preload_user = nginx" >> ${php_vars} && \
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

RUN sed -i -e 's/# en_US.UTF-8 UTF-8/en_US.UTF-8 UTF-8/' /etc/locale.gen && \
    sed -i -e 's/# pl_PL.UTF-8 UTF-8/pl_PL.UTF-8 UTF-8/' /etc/locale.gen && \
    sed -i -e 's/# de_DE.UTF-8 UTF-8/de_DE.UTF-8 UTF-8/' /etc/locale.gen && \
    sed -i -e 's/# sv_SE.UTF-8 UTF-8/sv_SE.UTF-8 UTF-8/' /etc/locale.gen && \
    locale-gen

ENV LANG=pl_PL.UTF-8
ENV LANGUAGE=pl_PL.UTF-8
ENV LC_ALL=pl_PL.UTF-8

COPY ./docker/php/cron /etc/cron.d/coyote
RUN chmod 0644 /etc/cron.d/coyote
RUN crontab /etc/cron.d/coyote

RUN curl -sS https://dl.yarnpkg.com/debian/pubkey.gpg | apt-key add -
RUN echo "deb https://dl.yarnpkg.com/debian/ stable main" | tee /etc/apt/sources.list.d/yarn.list

RUN apt-get update -yqq && apt-get install -y yarn

ADD ./docker/php/xdebug.sh /xdebug.sh
RUN chmod 755 /xdebug.sh

CMD ["/xdebug.sh"]
