FROM php:8.4-fpm-alpine

RUN apk add --no-cache \
    git curl zip unzip libpq-dev icu-dev oniguruma-dev linux-headers \
    $PHPIZE_DEPS \
    && docker-php-ext-install pdo_pgsql pgsql intl mbstring opcache pcntl bcmath \
    && pecl install redis \
    && docker-php-ext-enable redis \
    && apk del $PHPIZE_DEPS

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html

COPY docker/backend/php.ini /usr/local/etc/php/conf.d/wikiworld.ini
COPY docker/backend/opcache.ini /usr/local/etc/php/conf.d/opcache.ini

RUN addgroup -g 1000 wikiworld && adduser -G wikiworld -g wikiworld -s /bin/sh -D wikiworld
USER wikiworld

EXPOSE 9000
CMD ["php-fpm"]
