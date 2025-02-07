FROM php:8.3-fpm

RUN apt-get update && apt-get install -y \
    zip unzip git curl libpq-dev libonig-dev \
    && docker-php-ext-install pdo pdo_mysql mbstring opcache

# Instalacja Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html