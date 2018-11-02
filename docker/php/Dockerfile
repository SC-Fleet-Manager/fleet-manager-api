FROM php:7.2-fpm-alpine

RUN apk add --update --no-cache libintl icu icu-dev libxml2-dev \
    && docker-php-ext-install intl zip pdo pdo_mysql \
    && apk del icu-dev libxml2-dev; \
    apk --no-cache add --virtual .phpize-deps $PHPIZE_DEPS; \
    pecl install redis; \
    apk del .phpize-deps; \
    docker-php-ext-enable opcache redis

COPY install-composer.sh /tmp/install-composer.sh
RUN sh /tmp/install-composer.sh \
    && mv composer.phar /usr/local/bin/composer \
    && rm -f /tmp/install-composer.sh \
    ;
