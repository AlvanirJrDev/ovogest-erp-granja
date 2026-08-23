FROM php:8.3-fpm-alpine

RUN apk add --no-cache postgresql-dev icu-dev libzip-dev zip unzip git \
    && apk add --no-cache --virtual .build-deps $PHPIZE_DEPS \
    && docker-php-ext-install pdo_pgsql pgsql intl zip bcmath opcache pcntl \
    && apk del .build-deps

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html
