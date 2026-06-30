FROM php:8.2-cli

# Install system dependencies, composer, and Node.js/NPM
RUN apt-get update && apt-get install -y \
    git \
    unzip \
    libsqlite3-dev \
    curl \
    && curl -fsSL https://deb.nodesource.com/setup_20.x | bash - \
    && apt-get install -y openssl nodejs \
    && docker-php-ext-install pdo pdo_sqlite

COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

WORKDIR /var/www
COPY . .

# Create the missing database file
RUN mkdir -p database && touch database/database.sqlite

# Install PHP dependencies
RUN composer install --no-dev --optimize-autoloader

# Install Node dependencies and compile CSS/JS assets (Vite)
RUN npm install && npm run build

EXPOSE 8000

# Run migrations, seeders, and start the server automatically on boot
CMD php artisan migrate --force && php artisan db:seed --force && php artisan serve --host=0.0.0.0 --port=$PORT
