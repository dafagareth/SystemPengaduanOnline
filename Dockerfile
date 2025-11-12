FROM php:8.2-cli

# Install ekstensi PHP yang diperlukan
RUN docker-php-ext-install mysqli pdo pdo_mysql

# Set working directory
WORKDIR /var/www/html

# Expose port untuk PHP built-in server
EXPOSE 8000