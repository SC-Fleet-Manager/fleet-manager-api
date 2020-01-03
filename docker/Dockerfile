FROM php:7.4.1-fpm-alpine as req_php

# prod|dev
ARG ENV=prod

RUN apk add --no-cache --virtual .deps icu-libs zlib libintl libzip libxml2 libxslt
RUN apk add --no-cache --virtual .build-deps $PHPIZE_DEPS icu-dev libzip-dev libxml2-dev libxslt-dev libgcrypt-dev \
    && docker-php-ext-install intl zip pdo_mysql xml xsl bcmath \
    && pecl install redis \
    && apk del .build-deps \
    && docker-php-ext-enable --ini-name 05-opcache.ini opcache \
    && docker-php-ext-enable --ini-name 20-redis.ini redis \
    && rm -rf /tmp/*

WORKDIR /app

########################################
FROM req_php as composer_deps

COPY --from=composer:latest /usr/bin/composer /usr/local/bin/composer

# prod|dev
ARG ENV=prod

COPY src src
COPY .env* composer.json composer.lock symfony.lock ./

ENV COMPOSER_ALLOW_SUPERUSER 1
ENV COMPOSER_MEMORY_LIMIT -1
ENV APP_ENV $ENV
RUN composer global require "hirak/prestissimo:^0.3" --prefer-dist --no-progress --no-suggest --optimize-autoloader --classmap-authoritative --no-interaction; \
    if [ $ENV = 'prod' ]; then \
        composer install --no-scripts --no-suggest --ignore-platform-reqs --optimize-autoloader --classmap-authoritative --no-dev --prefer-dist --no-progress --no-interaction; \
        rm -rf vendor/*/*/docs; \
        find vendor -name *.md -exec rm -rf {} \; ; \
        find vendor -name LICENSE -exec rm -rf {} \; ; \
    else \
        composer install --no-scripts --no-suggest --optimize-autoloader --classmap-authoritative --prefer-dist --no-progress --no-interaction; \
    fi; \
    composer dump-env $ENV

################################
FROM node:12-alpine as node_deps

WORKDIR /app
COPY package.json yarn.lock ./
RUN yarn install

COPY assets assets
COPY .babelrc webpack.config.js postcss.config.js ./
RUN mkdir public && yarn encore production

####################################
FROM req_php as build_php

# prod|dev
ARG ENV=prod
ARG DUMP_ENV=0

COPY docker/php/php.prod.ini /usr/local/etc/php/php.ini
COPY docker/php/php-fpm.prod.conf /usr/local/etc/php-fpm.conf
COPY docker/php/www.prod.conf /usr/local/etc/php-fpm.d/www.conf
COPY docker/php/entrypoint.sh /usr/local/bin/docker-app-entrypoint
RUN chmod +x /usr/local/bin/docker-app-entrypoint

COPY assets assets
COPY bin/console bin/
COPY config config
COPY public/index.php public/
COPY src src
COPY templates templates
COPY translations translations
COPY .env* Makefile composer.json composer.lock ./
COPY --from=node_deps /app/public/build/entrypoints.json public/build/entrypoints.json
COPY --from=node_deps /app/public/build/manifest.json public/build/manifest.json
COPY --from=composer_deps /app/vendor vendor
COPY --from=composer_deps /app/.env.local.php .env.local.php

RUN if [ ${DUMP_ENV} == 0 ]; then echo "<?php return ['APP_ENV'=>'${ENV}'];" > .env.local.php; fi; \
    chown www-data .env*; \
    sed -i "s/^APP_ENV=.*$/APP_ENV=${ENV}/" .env; \
    if [ -e .env.local ]; then sed -i "s/^APP_ENV=.*$/APP_ENV=${ENV}/" .env.local; fi
RUN mkdir -p public/bundles \
    && bin/console assets:install -v \
    && rm -rf var/cache/* \
    && bin/console cache:warmup -v \
    && mkdir -p var/log && chown -R www-data var/log

ENV BUILD_ENV prod
ENTRYPOINT ["docker-app-entrypoint"]
CMD ["php-fpm"]

##############################
FROM httpd:2.4-alpine as build_apache

WORKDIR /app
COPY public/favicon.ico public/robots.txt public/
COPY public/icons public/icons
COPY --from=node_deps /app/public/build public/build
COPY --from=build_php /app/public/bundles public/bundles
RUN touch public/index.php

COPY docker/apache/httpd.conf /usr/local/apache2/conf/httpd.conf

##############################
FROM build_apache as build_apache_test

COPY docker/apache/httpd_test.conf /usr/local/apache2/conf/httpd.conf
RUN touch public/index_test.php

###############################
FROM build_php as build_php_dev

COPY public/index_test.php public/

COPY --from=composer:latest /usr/bin/composer /usr/local/bin/composer
ENV COMPOSER_MEMORY_LIMIT -1
ENV COMPOSER_ALLOW_SUPERUSER 1

# Xdebug install
ARG XDEBUG_VERSION=2.9.0
RUN set -eux; \
	apk add --no-cache --virtual .build-deps $PHPIZE_DEPS; \
	pecl install xdebug-$XDEBUG_VERSION; \
	apk del .build-deps; \
	rm -fr /tmp/*

RUN apk add --no-cache \
        chromium \
        chromium-chromedriver
ENV PANTHER_CHROME_DRIVER_BINARY /usr/lib/chromium/chromedriver
ENV PANTHER_NO_SANDBOX 1

COPY docker/php/php.dev.ini /usr/local/etc/php/php.ini
COPY docker/php/php-fpm.dev.conf /usr/local/etc/php-fpm.conf
COPY docker/php/www.dev.conf /usr/local/etc/php-fpm.d/www.conf

ENV BUILD_ENV dev
