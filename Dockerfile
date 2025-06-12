FROM composer:2.7 as build

WORKDIR /app

COPY . .

RUN composer install --no-dev --optimize-autoloader
RUN npm ci && npm run build

FROM php:8.2-fpm

COPY --from=build /app /var/www

RUN apt-get update && apt-get install -y \
    libzip-dev \
    libpng-dev \
    libjpeg-dev \
    libfreetype6-dev \
    libonig-dev \
    libxml2-dev \
    zip \
    git \
    unzip

RUN docker-php-ext-install pdo_mysql mbstring exif pcntl bcmath gd

COPY --from=build /app /var/www

CMD ["php-fpm"]