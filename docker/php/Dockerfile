FROM php:7.2-fpm-alpine

RUN apk add --update --no-cache libintl icu icu-dev libxml2-dev \
    && docker-php-ext-install intl zip pdo pdo_mysql \
    && docker-php-ext-enable opcache

COPY install-composer.sh /tmp/install-composer.sh
RUN sh /tmp/install-composer.sh \
    && mv composer.phar /usr/local/bin/composer \
    && rm -f /tmp/install-composer.sh \
    ;
