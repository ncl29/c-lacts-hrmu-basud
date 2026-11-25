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

# Ensure storage and cache directories exist with full write permissions
RUN mkdir -p storage/app/backups storage/app/public storage/framework/cache storage/framework/sessions storage/framework/views storage/logs bootstrap/cache \
    && chmod -R 777 storage bootstrap/cache

# Install production dependencies
RUN composer install --no-dev --optimize-autoloader --no-interaction

# Dynamic PORT assigned by cloud provider (Render / Railway)
ENV PORT=8000
EXPOSE 8000

# Container entrypoint
CMD ["sh", "-c", "mkdir -p database && touch database/database.sqlite && chmod 777 database/database.sqlite && php artisan key:generate --force && php artisan migrate --force --seed && php artisan serve --host=0.0.0.0 --port=${PORT}"]
