FROM php:8.3-apache

RUN docker-php-ext-install mysqli pdo pdo_mysql

# Enable Apache rewrite for common PHP routing needs.
RUN a2enmod rewrite

WORKDIR /var/www/html
