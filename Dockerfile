# Use the official PHP image with Apache
FROM php:8.2-apache

# Install basic extensions (including Curl for Cashfree)
RUN apt-get update && apt-get install -y \
    libcurl4-openssl-dev \
    && docker-php-ext-install curl

# Copy your files to the web server folder
COPY . /var/www/html/

# Allow Apache to use .htaccess files (optional but good practice)
RUN a2enmod rewrite

# Tell Render we are listening on port 80
EXPOSE 80