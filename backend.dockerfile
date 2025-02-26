FROM php:8.3-apache

# Install dependencies
RUN apt-get update && apt-get install -y \
    libzip-dev \
    zip \
    && docker-php-ext-install mysqli pdo_mysql zip

# Copy application files
COPY api/ /var/www/html/
RUN chown -R www-data:www-data /var/www/html

# Install Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer
WORKDIR /var/www/html
RUN composer install --no-dev

# Enable Apache modules
RUN a2enmod rewrite headers

# Copy Apache VirtualHost configuration
COPY 000-default.conf /etc/apache2/sites-available/000-default.conf

# Set Apache ServerName
RUN echo "ServerName localhost" >> /etc/apache2/apache2.conf

# Restart Apache
RUN service apache2 restart
