FROM php:8.3-cli

RUN apt-get update && apt-get install -y \
    unzip git curl libzip-dev \
    && docker-php-ext-install pdo pdo_mysql

WORKDIR /app

COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

COPY . .

RUN composer install --no-dev --optimize-autoloader

EXPOSE 8080

# CMD php artisan config:clear && \
#     php artisan migrate --force && \
#     php artisan serve --host=0.0.0.0 --port=8080

    CMD php artisan migrate --force && php artisan serve --host=0.0.0.0 --port=$PORT