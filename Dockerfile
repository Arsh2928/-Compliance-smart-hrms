FROM php:8.2-apache

RUN apt-get update && apt-get install -y \
    git \
    unzip \
    libonig-dev \
    libzip-dev \
    zlib1g-dev \
    libpng-dev \
    libjpeg-dev \
    libfreetype6-dev \
    curl \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install gd pdo pdo_mysql zip \
    && pecl install mongodb \
    && docker-php-ext-enable mongodb \
    && a2enmod rewrite \
    && sed -ri -e 's!/var/www/html!/var/www/html/public!g' /etc/apache2/sites-available/*.conf \
    && rm -rf /var/lib/apt/lists/*

COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

ARG APP_ENV=production
ENV APP_ENV=${APP_ENV}

WORKDIR /var/www/html

COPY . .

RUN echo "ServerName localhost" >> /etc/apache2/apache2.conf \
    && sed -ri -e 's|/var/www/html|/var/www/html/public|g' /etc/apache2/sites-available/*.conf /etc/apache2/apache2.conf

RUN if [ "$APP_ENV" = "local" ] && [ ! -f .env ] && [ -f .env.example ]; then cp .env.example .env; fi \
    && composer install --no-dev --optimize-autoloader --no-interaction --prefer-dist \
    && if [ -f .env ]; then php artisan key:generate --force; fi \
    && chmod -R 775 storage bootstrap/cache \
    && chown -R www-data:www-data storage bootstrap/cache

EXPOSE 80

CMD ["apache2-foreground"]
