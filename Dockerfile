FROM php:8.2-cli

# Install system dependencies and composer
RUN apt-get update && apt-get install -y \
    git \
    unzip \
    libsqlite3-dev \
    && docker-php-ext-install pdo pdo_sqlite

COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Set working directory
WORKDIR /var/www

# Copy project files
COPY . .

# Install PHP dependencies
# Create the missing database file
RUN mkdir -p database && touch database/database.sqlite

RUN composer install --no-dev --optimize-autoloader

# Expose the port Laravel will run on
EXPOSE 8000

# Run migrations, seeders, and start the server automatically on boot
CMD php artisan migrate --force && php artisan db:seed --force && php artisan serve --host=0.0.0.0 --port=$PORT
