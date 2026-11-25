FROM php:8.2-cli-alpine

# Install system dependencies & build tools
RUN apk add --no-cache \
    zip \
    unzip \
    libzip-dev \
    libpng-dev \
    sqlite-dev \
    oniguruma-dev

# Install required PHP extensions
RUN docker-php-ext-install pdo pdo_sqlite pdo_mysql mbstring zip bcmath

# Install Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Set working directory
WORKDIR /var/www

# Copy application files
COPY . .

# Set up .env and permissions before composer install
RUN cp .env.example .env \
    && mkdir -p database storage/app/backups storage/app/public storage/framework/cache/data storage/framework/sessions storage/framework/views storage/logs bootstrap/cache \
    && touch database/database.sqlite \
    && chmod -R 777 database storage bootstrap/cache

# Default production environment variables
ENV APP_ENV=production \
    APP_DEBUG=false \
    DB_CONNECTION=sqlite \
    DB_DATABASE=/var/www/database/database.sqlite \
    PORT=8000

# Install production dependencies
RUN composer install --no-dev --optimize-autoloader --no-interaction

EXPOSE 8000

# Container entrypoint: generate key, migrate & seed sqlite, start server
CMD ["sh", "-c", "php artisan key:generate --force && php artisan migrate --force --seed && php artisan serve --host=0.0.0.0 --port=${PORT}"]
