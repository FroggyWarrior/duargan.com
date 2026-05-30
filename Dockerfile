# Use PHP with Apache, perfectly mimicking standard shared hosting
FROM php:8.2-apache

# Install system dependencies for GD and image processing
RUN apt-get update && apt-get install -y \
    libpng-dev \
    libjpeg62-turbo-dev \
    libwebp-dev \
    libfreetype6-dev \
    && docker-php-ext-configure gd --with-freetype --with-jpeg --with-webp \
    && docker-php-ext-install -j$(nproc) gd pdo pdo_mysql

# Enable Apache mod_rewrite (essential for MVC routing to index.php)
RUN a2enmod rewrite

# Change Apache's document root to our new secure public folder
ENV APACHE_DOCUMENT_ROOT /var/www/html/public

# Update the default Apache site configuration to use the new root
RUN sed -ri -e 's!/var/www/html!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/sites-available/*.conf
RUN sed -ri -e 's!/var/www/!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/apache2.conf /etc/apache2/conf-available/*.conf

# Allow access to public directory
RUN echo "<Directory /var/www/html/public>" >> /etc/apache2/apache2.conf \
    && echo "    Options Indexes FollowSymLinks" >> /etc/apache2/apache2.conf \
    && echo "    AllowOverride All" >> /etc/apache2/apache2.conf \
    && echo "    Require all granted" >> /etc/apache2/apache2.conf \
    && echo "</Directory>" >> /etc/apache2/apache2.conf

# Set the web server user (www-data) as the owner of the application directory
RUN chown -R www-data:www-data /var/www/html