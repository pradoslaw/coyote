FROM php:7.0.8-fpm

RUN apt-get update && \
    apt-get install -y libmcrypt-dev libpq-dev \
    mysql-client libmagickwand-dev --no-install-recommends \
    && pecl install imagick \
    && docker-php-ext-enable imagick \
    && docker-php-ext-install mcrypt

RUN pecl install mongodb \
    && docker-php-ext-enable mongodb

RUN docker-php-ext-install pdo pdo_pgsql pdo_mysql gd mbstring mcrypt zip

RUN curl -sS http://dl.yarnpkg.com/debian/pubkey.gpg | apt-key add -
RUN echo "deb http://dl.yarnpkg.com/debian/ stable main" | tee /etc/apt/sources.list.d/yarn.list

RUN curl -sL https://deb.nodesource.com/setup_6.x | bash -
RUN apt-get update && apt-get install -qq -y --no-install-recommends nodejs yarn

RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer
RUN composer --version
