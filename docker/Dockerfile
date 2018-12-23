########################################
FROM php:7.2-fpm-alpine as composer_deps

COPY --from=composer:latest /usr/bin/composer /usr/local/bin/composer

WORKDIR /app
COPY src src
COPY .env composer.json composer.lock symfony.lock ./

ENV COMPOSER_ALLOW_SUPERUSER 1
ENV COMPOSER_MEMORY_LIMIT -1
ENV APP_ENV prod
RUN composer global require "hirak/prestissimo:^0.3" --prefer-dist --no-progress --no-suggest --optimize-autoloader --classmap-authoritative --no-interaction
RUN composer install --no-scripts --no-suggest --optimize-autoloader --classmap-authoritative --no-dev --prefer-dist --no-progress --no-interaction

################################
FROM node:10-alpine as node_deps

WORKDIR /app
COPY package.json yarn.lock ./
RUN yarn install

COPY assets assets
COPY webpack.config.js postcss.config.js ./
RUN mkdir public && yarn encore production

####################################
FROM php:7.2-fpm-alpine as build_php

RUN apk add --no-cache --virtual .persistent-deps \
		icu-libs \
        zlib \
        libintl \
        libzip
RUN apk add --no-cache --virtual .build-deps \
        $PHPIZE_DEPS \
        icu-dev \
        libxml2-dev \
        libzip-dev \
    && docker-php-ext-install \
        intl \
        zip \
        pdo \
        pdo_mysql \
    && pecl install redis \
    && apk del .build-deps \
    && docker-php-ext-enable --ini-name 05-opcache.ini opcache \
    && docker-php-ext-enable --ini-name 20-redis.ini redis

COPY docker/php/php.prod.ini /usr/local/etc/php/php.ini
COPY docker/php/entrypoint.sh /usr/local/bin/docker-app-entrypoint
RUN chmod +x /usr/local/bin/docker-app-entrypoint

WORKDIR /app
COPY bin bin
COPY config config
COPY public public
COPY src src
COPY templates templates
COPY translations translations
COPY .env Makefile composer.json composer.lock ./
COPY --from=node_deps /app/public/build/manifest.json public/build/manifest.json
COPY --from=composer_deps /app/vendor vendor

RUN sed -i 's/^APP_ENV=.*$/APP_ENV=prod/' .env
RUN mkdir -p public/bundles && bin/console assets:install \
    && rm -fr var/cache/* \
    && bin/console cache:warmup \
    && mkdir -p var/log && chown -R www-data var/log

ENV BUILD_ENV prod
ENTRYPOINT ["docker-app-entrypoint"]
CMD ["php-fpm"]

##############################
FROM httpd:2.4-alpine as build_apache

WORKDIR /app
COPY public public
COPY --from=node_deps /app/public/build public/build
COPY --from=build_php /app/public/bundles public/bundles

COPY docker/apache/httpd.conf /usr/local/apache2/conf/httpd.conf

###############################
FROM build_php as build_php_dev

COPY --from=composer:latest /usr/bin/composer /usr/local/bin/composer
ENV COMPOSER_MEMORY_LIMIT -1
ENV COMPOSER_ALLOW_SUPERUSER 1

ARG XDEBUG_VERSION=2.6.0
#ARG XDEBUG_VERSION=2.7.0beta1
RUN set -eux; \
	apk add --no-cache --virtual .build-deps $PHPIZE_DEPS; \
	pecl install xdebug-$XDEBUG_VERSION; \
	docker-php-ext-enable xdebug; \
	apk del .build-deps

RUN apk add --no-cache \
        chromium \
        chromium-chromedriver
ENV PANTHER_CHROME_DRIVER_BINARY /usr/lib/chromium/chromedriver
ENV PANTHER_NO_SANDBOX 1

COPY docker/php/php.dev.ini /usr/local/etc/php/php.ini

ENV BUILD_ENV dev
