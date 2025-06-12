FROM composer:2.7 as build

WORKDIR /app

COPY . .

RUN apk add --no-cache nodejs npm

RUN composer install --no-dev --optimize-autoloader
RUN npm ci && npm run build

FROM php:8.2-fpm

WORKDIR /var/www

RUN apt-get update && apt-get install -y curl unzip git \
    && curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

COPY --from=build /app /var/www

RUN apt update && apt install -y libpng-dev libonig-dev libxml2-dev zip unzip git curl
RUN docker-php-ext-install pdo_mysql mbstring exif pcntl bcmath gd

RUN chown -R www-data:www-data /var/www/storage /var/www/bootstrap/cache \
    && chmod -R 775 /var/www/storage /var/www/bootstrap/cache

CMD ["php-fpm"]