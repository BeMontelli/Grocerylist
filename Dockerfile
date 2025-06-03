FROM php:8.2-fpm

RUN apt-get update \
    && apt-get install -y libicu-dev zip unzip git \
    && docker-php-ext-install pdo pdo_mysql intl opcache

# Extensions facultatives
# RUN pecl install apcu xdebug \
#     && docker-php-ext-enable apcu xdebug

# Installer Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

COPY Docker/php.ini /usr/local/etc/php/php.ini