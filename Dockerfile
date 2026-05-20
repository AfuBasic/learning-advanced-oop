FROM php:8.5-fpm-alpine

# Install core utilities and database drivers for PHP 8.5
RUN apk add --no-cache bash \
    && docker-php-ext-install pdo pdo_mysql

WORKDIR /var/www