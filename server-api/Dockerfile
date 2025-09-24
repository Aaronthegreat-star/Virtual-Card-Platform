FROM php:8.2-fpm AS builder

RUN apt-get update && apt-get install -y \
    libonig-dev libxml2-dev libzip-dev libicu-dev unzip curl \
    && docker-php-ext-install -j$(nproc) \
        mbstring pdo pdo_mysql zip bcmath fileinfo intl opcache \
    && curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer \
    && apt-get autoremove -y && apt-get clean && rm -rf /var/lib/apt/lists/* /tmp/* /var/tmp/*

WORKDIR /var/www/html

RUN useradd -m laravel && chown -R laravel:laravel /var/www/html

COPY --chown=laravel:laravel server-api/composer.json /var/www/html/
COPY --chown=laravel:laravel server-api/composer.lock* /var/www/html/

# Copy the entire application first (needed for artisan)
COPY server-api/ /var/www/html/

RUN composer install --optimize-autoloader --verbose

RUN chown -R laravel:laravel /var/www/html

USER laravel

FROM php:8.2-fpm AS runtime

# Install runtime dependencies and PHP extensions
RUN apt-get update && apt-get install -y \
    libonig-dev libxml2-dev libzip-dev libicu-dev \
    && docker-php-ext-install -j$(nproc) \
        mbstring pdo pdo_mysql zip bcmath fileinfo intl opcache \
    && apt-get autoremove -y && apt-get clean && rm -rf /var/lib/apt/lists/*

WORKDIR /var/www/html

RUN useradd -m laravel && chown -R laravel:laravel /var/www/html

COPY --chown=laravel:laravel --from=builder /var/www/html /var/www/html

USER laravel

EXPOSE 9000

CMD ["php-fpm"]





