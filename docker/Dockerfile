FROM php:8.3-fpm

RUN apt-get update && apt-get install -y \
    zip unzip git curl libpq-dev libonig-dev \
    npm \
    && docker-php-ext-install pdo pdo_mysql mbstring opcache

# Instalacja Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

RUN curl -sS https://get.symfony.com/cli/installer | bash && \
    mv /root/.symfony*/bin/symfony /usr/local/bin/symfony



WORKDIR /var/www/html

#ENTRYPOINT ["./docker/entrypoint.sh"]