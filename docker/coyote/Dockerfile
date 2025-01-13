FROM 4programmers/php-node:8.3.2-20 as backend

USER root
WORKDIR /app

COPY package.json yarn.lock babel.config.js .browserslistrc webpack.common.js webpack.dev.js webpack.prod.js tsconfig.json /app/
RUN yarn install

COPY webpack.common.js webpack.dev.js webpack.prod.js /app/
RUN true
COPY resources /app/resources
RUN true
COPY survey /app/survey
RUN true
COPY public /app/public

RUN ls /app -lsa

ARG FRONTEND_SENTRY_DSN
ARG VAPID_PUBLIC_KEY
ARG RELEASE
ARG SENTRY_API_KEY

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

ADD ./docker/coyote/start.sh /start.sh
RUN chmod 755 /start.sh

RUN chmod 0775 -R storage/
RUN chmod 0775 bootstrap/cache/
RUN chown -R nginx:nginx /app

CMD ["/start.sh"]
