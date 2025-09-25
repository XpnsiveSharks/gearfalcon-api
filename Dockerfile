# Multi-stage build for smaller production image
FROM php:8.3-apache as base

# Add metadata labels
LABEL maintainer="XpnsiveSharks <aban.marynelle.tesoro@gmail.com>"
LABEL version="1.0"
LABEL description="GearFalcon API Docker Image"

# Install system dependencies
RUN apt-get update && apt-get install -y \
    git \
    curl \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    libzip-dev \
    zip \
    unzip \
    && docker-php-ext-install pdo_mysql mbstring exif pcntl bcmath gd zip \
    && apt-get clean \
    && rm -rf /var/lib/apt/lists/*

# Get latest Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Create non-root user for security
RUN groupadd -r gearfalcon && useradd -r -g gearfalcon gearfalcon

# Set working directory
WORKDIR /var/www/html

# Copy composer files first for better layer caching
COPY --chown=gearfalcon:gearfalcon composer.json composer.lock* ./

# Install PHP dependencies
RUN composer install --no-dev --optimize-autoloader --no-interaction

# Copy application code
COPY --chown=gearfalcon:gearfalcon . .

# Set proper permissions
RUN chmod -R 755 /var/www/html

# Enable Apache mod_rewrite
RUN a2enmod rewrite

# Copy custom Apache configuration instead of using sed
COPY <<EOF /etc/apache2/sites-available/000-default.conf
<VirtualHost *:80>
    ServerName localhost
    DocumentRoot /var/www/html/public

    <Directory /var/www/html/public>
        Options Indexes FollowSymLinks
        AllowOverride All
        Require all granted
    </Directory>

    ErrorLog \${APACHE_LOG_DIR}/error.log
    CustomLog \${APACHE_LOG_DIR}/access.log combined
</VirtualHost>
EOF

# Add health check
HEALTHCHECK --interval=30s --timeout=3s --start-period=5s --retries=3 \
    CMD curl -f http://localhost/ || exit 1

# Switch to non-root user
USER gearfalcon

# Expose port 80
EXPOSE 80

# Start Apache
CMD ["apache2-foreground"]
