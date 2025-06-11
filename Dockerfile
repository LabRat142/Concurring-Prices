FROM php:8.2

# Install system dependencies, PHP extensions, Composer, Python, and pip
RUN apt-get update -y && apt-get install -y \
    openssl \
    zip \
    unzip \
    git \
    python3 \
    python3-venv \
    python3-pip \
    && docker-php-ext-install pdo pdo_mysql mbstring bcmath gd \
    && curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

# Set working directory to /app
WORKDIR /app

# Copy the Laravel app into the container's /app directory
COPY . /app

# Install PHP dependencies
RUN composer install

# Set up the Python virtual environment
RUN python3 -m venv /opt/venv \
    && /opt/venv/bin/pip install --no-cache-dir -r /app/requirements.txt

# Ensure storage and cache are writable
RUN chown -R www-data:www-data /app/storage /app/bootstrap/cache

# Set environment for Python venv
ENV PATH="/opt/venv/bin:$PATH"

#Generate app key
CMD php artisan key:generate

# Start Laravel server (use PHP's built-in server for local dev)
ENTRYPOINT ["php", "artisan", "serve", "--host=0.0.0.0", "--port=8000"]

# Expose port 8000 for the PHP built-in server
EXPOSE 8000
