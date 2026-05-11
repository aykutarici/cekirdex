FROM ininia-web-laravel-app:latest

WORKDIR /var/www/html

RUN apk add --no-cache \
    autoconf \
    bash \
    curl \
    g++ \
    git \
    make \
    unzip \
    zip

RUN if ! php -m | grep -qi '^redis$'; then pecl install redis && docker-php-ext-enable redis; fi

COPY docker/backend/php.ini /usr/local/etc/php/conf.d/99-cekirdex.ini

COPY composer.json composer.lock ./
RUN composer install --no-dev --no-interaction --prefer-dist --optimize-autoloader --no-scripts

COPY . .

RUN composer dump-autoload --optimize \
  && chown -R www-data:www-data storage bootstrap/cache

CMD ["php-fpm"]
