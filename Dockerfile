# Use PHP with Apache, perfectly mimicking standard shared hosting
FROM php:8.2-apache

# Install PDO MySQL extensions so PHP can talk to MariaDB
RUN docker-php-ext-install pdo pdo_mysql

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