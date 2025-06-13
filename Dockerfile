FROM php:8.2

# Install system dependencies, PHP extensions, Composer, Python, and pip
RUN apt-get update -y && apt-get install -y \
    libonig-dev \
    libpng-dev \
    zip \
    unzip \
    python3 \
    python3-venv \
    && docker-php-ext-install pdo pdo_mysql mbstring bcmath gd \
    && curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

# Set working directory to /app
WORKDIR /app

# Copy the Laravel app into the container's /app directory
COPY . /app

# Install PHP dependencies via Composer
RUN composer install --no-interaction --prefer-dist

# Create and activate a Python virtual environment, then install dependencies
RUN python3 -m venv /app/venv \
    && /app/venv/bin/pip install --no-cache-dir -r requirements.txt

# Ensure storage and cache are writable by the www-data user (PHP's default user)
RUN chown -R www-data:www-data /app/storage /app/bootstrap/cache

# Start Laravel server (use PHP's built-in server for local dev)
# Generate app key and then start the Laravel server
ENTRYPOINT ["/bin/sh", "-c", "php artisan key:generate && php artisan serve --host=0.0.0.0 --port=8000"]

# Expose port 8000 for the PHP built-in server
EXPOSE 8000
