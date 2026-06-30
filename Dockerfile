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
RUN composer install --no-dev --optimize-autoloader

# Expose the port Laravel will run on
EXPOSE 8000

# Start Laravel server using the environment variable port
CMD php artisan serve --host=0.0.0.0 --port=$PORT
