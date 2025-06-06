FROM php:8.2-apache

RUN apt-get update && apt-get install -y \
    git unzip curl libzip-dev libonig-dev libxml2-dev zip && \
    docker-php-ext-install pdo pdo_mysql zip

COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

COPY . /var/www/html
WORKDIR /var/www/html

RUN composer install --no-interaction --optimize-autoloader

RUN chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache

RUN a2enmod rewrite

COPY ./000-default.conf /etc/apache2/sites-available/000-default.conf
