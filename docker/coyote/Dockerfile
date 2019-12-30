FROM 4programmers/php-node:latest as backend

WORKDIR /app

COPY package.json yarn.lock .babelrc webpack.common.js webpack.dev.js webpack.prod.js /app/
RUN yarn install

COPY webpack.common.js webpack.dev.js webpack.prod.js /app/
COPY resources/assets /app/resources/assets
COPY public /app/public

RUN yarn run prod

RUN rm -rf node_modules

COPY composer.json composer.json
COPY composer.lock composer.lock
RUN composer install --prefer-dist --no-scripts --no-dev --no-autoloader

COPY . .

RUN composer dump-autoload --no-scripts --no-dev --optimize
# clear cache
RUN composer clearcache && yarn cache clean
RUN rm -rf /app/.composer && rm -rf /root/.composer && rm -rf  /usr/local/share/.cache/yarn/

RUN chown -R nginx:nginx /app
RUN chmod 0775 -R storage/
RUN chmod 0775 bootstrap/cache/
