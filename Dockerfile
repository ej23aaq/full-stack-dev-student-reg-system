FROM php:8.3-apache

# Install PDO and PDO MySQL
RUN docker-php-ext-install pdo pdo_mysql
