FROM php:8.3-fpm-alpine

# Install system dependencies
RUN apk update && apk add --no-cache \
    git \
    curl \
    libpng-dev \
    libjpeg-turbo-dev \
    libwebp-dev \
    libxpm-dev \
    libonig-dev \
    libxml2-dev \
    sqlite-dev \
    zip \
    unzip \
    nodejs \
    npm \
    libpng \
    libjpeg \
    libwebp \
    libxpm \
    libonig \
    libxml2 \
    sqlite

# Install PHP extensions
RUN docker-php-ext-configure gd --with-freetype --with-jpeg --with-webp \
    && docker-php-ext-install pdo_sqlite mbstring gd exif

# Install Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Set working directory
WORKDIR /var/www/html

# Copy composer files
COPY composer.json ./

# Install dependencies
RUN composer install --no-dev --no-scripts --no-autoloader

# Copy application files
COPY . .

# Install npm dependencies and build
RUN npm install
RUN npm run build

# Complete composer install
RUN composer dump-autoload --optimize
RUN composer install --no-dev --optimize-autoloader --no-interaction

# Create necessary directories
RUN mkdir -p storage/framework/{cache,sessions,views} \
    && mkdir -p storage/logs \
    && mkdir -p bootstrap/cache \
    && chmod -R 775 storage bootstrap/cache

# Expose port
EXPOSE 8000

# Start server
CMD php artisan serve --host=0.0.0.0 --port=8000