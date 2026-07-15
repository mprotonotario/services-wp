FROM php:8.4-cli-alpine

# Install system dependencies and php extensions
RUN apk add --no-cache \
    bash \
    git \
    curl \
    libpng-dev \
    libxml2-dev \
    zip \
    unzip \
    libzip-dev \
    oniguruma-dev \
    $PHPIZE_DEPS \
    && docker-php-ext-install pdo_mysql mbstring zip pcntl bcmath gd \
    && pecl install redis \
    && docker-php-ext-enable redis \
    && apk del $PHPIZE_DEPS

# Install Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Set working directory
WORKDIR /var/www

# Copy codebase
COPY . .

# Install dependencies (ignoring scripts for now)
RUN composer install --no-interaction --optimize-autoloader --no-dev

# Setup entrypoint script
RUN chmod +x docker-entrypoint.sh

# Expose port 8000
EXPOSE 8000

# Run entrypoint
ENTRYPOINT ["/var/www/docker-entrypoint.sh"]
