FROM php:8.0-fpm-buster as runtime_php

RUN apt-get update && apt-get install -y \
    unzip \
    procps \
    lsof \
    libzip-dev \
    libxml2-dev \
    libxslt-dev \
    libgcrypt-dev \
    libpq-dev \
    && docker-php-ext-install -j$(nproc) intl zip pdo_pgsql xml xsl bcmath \
    && pecl channel-update pecl.php.net \
    && pecl install redis apcu \
    && apt-get remove -y icu-devtools libicu-dev libzip-dev libxml2-dev libxslt-dev libgcrypt-dev libpq-dev \
    && rm -rf /var/lib/apt/lists/* \
    && docker-php-ext-enable --ini-name 05-opcache.ini opcache \
    && docker-php-ext-enable --ini-name 10-apcu.ini apcu \
    && docker-php-ext-enable --ini-name 20-redis.ini redis \
    && docker-php-source delete \
    && rm -rf /tmp/*

RUN mkdir -p /app /.composer && chown -R www-data:www-data /app /.composer

WORKDIR /app

########################################
FROM runtime_php as composer_deps

COPY --from=composer:2 /usr/bin/composer /usr/local/bin/composer

# prod|beta|dev
ARG ENV=prod

COPY .env* composer.json composer.lock symfony.lock ./

ENV COMPOSER_ALLOW_SUPERUSER 1
ENV COMPOSER_MEMORY_LIMIT -1
ENV COMPOSER_HOME /.composer
ENV APP_ENV $ENV
RUN if [ $ENV = 'prod' ] || [ $ENV = 'beta' ]; then \
        composer install --no-scripts --no-suggest --no-autoloader --prefer-dist --no-progress --no-interaction --no-dev --ignore-platform-reqs; \
    else \
        composer install --no-scripts --no-suggest --no-autoloader --prefer-dist --no-progress --no-interaction; \
    fi;

COPY src src
RUN if [ $ENV = 'prod' ] || [ $ENV = 'beta' ]; then \
        composer dump-autoload --optimize --classmap-authoritative --no-dev; \
    else \
        composer dump-autoload --optimize --classmap-authoritative; \
    fi; \
    composer dump-env $ENV;

####################################
FROM runtime_php as build_php

# Symfony APP_ENV
ARG ENV=prod
ARG DUMP_ENV=0

COPY docker/php/php-fpm-fcgi.prod.ini /usr/local/etc/php/php-fpm-fcgi.ini
COPY docker/php/php-cli.prod.ini /usr/local/etc/php/php-cli.ini
COPY docker/php/php-fpm.prod.conf /usr/local/etc/php-fpm.conf
COPY docker/php/www.prod.conf /usr/local/etc/php-fpm.d/www.conf
COPY docker/php/entrypoint.sh /usr/local/bin/docker-app-entrypoint
RUN chmod +x /usr/local/bin/docker-app-entrypoint

COPY --from=composer_deps /app/vendor vendor
COPY .env* ./
COPY --from=composer_deps /app/.env.local.php .env.local.php
COPY bin/console bin/
COPY config config
COPY migrations migrations
COPY public/index.php public/
COPY src src
COPY templates templates
COPY Makefile composer.json composer.lock ./

RUN if [ ${DUMP_ENV} -eq 0 ]; then echo "<?php return ['APP_ENV'=>'${ENV}','APP_SECRET'=>''];" > .env.local.php; fi; \
    sed -i "s/^APP_ENV=.*$/APP_ENV=${ENV}/" .env; \
    if [ -e .env.local ]; then sed -i "s/^APP_ENV=.*$/APP_ENV=${ENV}/" .env.local; fi
RUN mkdir -p public/bundles var/log var/cache \
    && bin/console cache:warmup -v \
    && bin/console assets:install public -v \
    && chown -R www-data:www-data var/log var/cache

USER www-data

ENTRYPOINT ["docker-app-entrypoint"]
CMD ["php-fpm"]

###############################
FROM build_php as build_supervisor

USER root
RUN apt-get update && apt-get install -y supervisor \
    && rm -rf /var/lib/apt/lists/* /tmp/*
COPY docker/supervisor/app.prod.conf /etc/supervisor/conf.d/app.conf
COPY docker/supervisor/supervisord.conf /etc/supervisor/supervisord.conf

ENTRYPOINT ["docker-app-entrypoint"]
CMD ["/usr/bin/supervisord", "-c", "/etc/supervisor/supervisord.conf"]

##############################
FROM httpd:2.4 as build_apache

ENV APACHE_RUN_USER www-data
ENV APACHE_RUN_GROUP www-data

RUN apt-get update && apt-get install -y curl \
    && rm -rf /var/lib/apt/lists/* \
    && rm -rf /tmp/*

COPY docker/apache/httpd.conf /usr/local/apache2/conf/httpd.conf

WORKDIR /app

COPY public public

###############################
FROM build_php as build_php_dev

USER root
COPY --from=composer:2 /usr/bin/composer /usr/local/bin/composer
ENV COMPOSER_MEMORY_LIMIT -1
ENV COMPOSER_ALLOW_SUPERUSER 1
ENV COMPOSER_HOME /.composer
ENV PANTHER_NO_SANDBOX 1

COPY docker/php/php-fpm-fcgi.dev.ini /usr/local/etc/php/php-fpm-fcgi.ini
COPY docker/php/php-cli.dev.ini /usr/local/etc/php/php-cli.ini
COPY docker/php/php-fpm.dev.conf /usr/local/etc/php-fpm.conf
COPY docker/php/www.dev.conf /usr/local/etc/php-fpm.d/www.conf

# Xdebug install
ARG XDEBUG_VERSION=3.0.3
RUN set -eux; \
    apt-get update && apt-get install -y $PHPIZE_DEPS git \
	&& pecl install xdebug-$XDEBUG_VERSION \
    && apt-get remove -y $PHPIZE_DEPS \
    && rm -rf /var/lib/apt/lists/* /tmp/*

COPY bin/phpunit bin/
COPY tests tests
COPY phpunit.xml.dist ./

RUN composer dump-autoload --optimize --classmap-authoritative; \
    composer dump-env $ENV; \
    # install phpunit
    bin/phpunit --version;

USER www-data

###############################
FROM build_php_dev as build_supervisor_dev

USER root
RUN apt-get update && apt-get install -y supervisor \
    && rm -rf /var/lib/apt/lists/* /tmp/*
COPY docker/supervisor/app.conf /etc/supervisor/conf.d/app.conf
COPY docker/supervisor/supervisord.conf /etc/supervisor/supervisord.conf

ENTRYPOINT ["docker-app-entrypoint"]
CMD ["/usr/bin/supervisord", "-c", "/etc/supervisor/supervisord.conf"]
