FROM php:8.1-fpm

# Install system dependencies
RUN apt-get update && apt-get install -y \
    git \
    curl \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    zip \
    unzip \
    libpq-dev

# Clear cache
RUN apt-get clean && rm -rf /var/lib/apt/lists/*

# Install PHP extensions
RUN docker-php-ext-install pdo_pgsql mbstring exif pcntl bcmath gd

# Configure PHP for better performance
RUN echo "memory_limit=256M" > /usr/local/etc/php/conf.d/memory-limit.ini && \
    echo "upload_max_filesize=100M" >> /usr/local/etc/php/conf.d/uploads.ini && \
    echo "post_max_size=100M" >> /usr/local/etc/php/conf.d/uploads.ini && \
    echo "max_execution_time=600" >> /usr/local/etc/php/conf.d/timeout.ini && \
    echo "opcache.enable=1" >> /usr/local/etc/php/conf.d/opcache.ini && \
    echo "opcache.memory_consumption=256" >> /usr/local/etc/php/conf.d/opcache.ini && \
    echo "opcache.max_accelerated_files=20000" >> /usr/local/etc/php/conf.d/opcache.ini && \
    echo "opcache.validate_timestamps=0" >> /usr/local/etc/php/conf.d/opcache.ini

# Get latest Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Set working directory
WORKDIR /var/www

# Copy composer files first
COPY composer.json composer.lock ./

# Install dependencies
RUN composer install --no-scripts --no-autoloader

# Copy existing application directory contents
COPY . .

# Generate autoloader and run scripts
RUN composer dump-autoload --optimize && \
    composer run-script post-autoload-dump

# Set permissions
RUN chown -R www-data:www-data /var/www/storage /var/www/bootstrap/cache && \
    chmod -R 775 /var/www/storage /var/www/bootstrap/cache

# Expose port 8000 for artisan serve
EXPOSE 8000

# Start Laravel development server
CMD ["php", "artisan", "serve", "--host=0.0.0.0", "--port=8000"]
