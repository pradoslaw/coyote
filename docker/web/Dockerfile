FROM 4programmers/php-node:latest as backend

WORKDIR /var/www

COPY package.json yarn.lock .babelrc /var/www/
RUN yarn install

COPY webpack.common.js webpack.dev.js webpack.prod.js /var/www/
COPY resources/assets /var/www/resources/assets
COPY public /var/www/public

RUN yarn run prod

RUN rm -rf node_modules

COPY composer.json composer.json
COPY composer.lock composer.lock
RUN composer install --prefer-dist --no-scripts --no-dev --no-autoloader && rm -rf /root/.composer

COPY . .
RUN composer dump-autoload --no-scripts --no-dev --optimize && composer clearcache && yarn cache clean && rm -rf /var/www/.composer

RUN mkdir -p /run/nginx
RUN apk update && apk add nginx openrc

#RUN ln -sf /dev/stdout /var/log/nginx/access.log && ln -sf /dev/stderr /var/log/nginx/error.log

RUN chown -R nginx:nginx /var/www
RUN chmod 0775 -R storage/
RUN chmod 0775 bootstrap/cache/

COPY docker/nginx/nginx.conf /etc/nginx/conf.d/default.conf

EXPOSE 443 80
