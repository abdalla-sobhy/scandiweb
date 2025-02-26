FROM php:8.3-apache

# Install dependencies
RUN apt-get update && apt-get install -y \
    libzip-dev \
    zip \
    && docker-php-ext-install mysqli pdo_mysql zip

# Configure Apache
COPY api/ /var/www/html/
RUN chown -R www-data:www-data /var/www/html

# Install Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer
RUN composer install --no-dev

# Enable modules
RUN a2enmod rewrite headers

# Configure Apache ServerName
RUN echo "ServerName localhost" >> /etc/apache2/apache2.conf
