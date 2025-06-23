# Use official PHP 8.2 base image with CLI
FROM php:8.2-cli

# Install required system packages and PHP extensions
RUN apt-get update && apt-get install -y \
    unzip \
    libpq-dev \
    && docker-php-ext-install pdo pdo_pgsql

# Set working directory
WORKDIR /app

# Copy app files into container
COPY . .

# Install Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer
RUN composer install --no-dev --optimize-autoloader

# Expose Render internal port
EXPOSE 10000

# Start Slim app using built-in PHP server
CMD ["php", "-S", "0.0.0.0:10000", "-t", "public"]
