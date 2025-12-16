# Use an official PHP image with Apache built-in
FROM php:8.4-apache
# You can choose a different version e.g., 8.3-apache

# Switch to root so we can do root things
USER root

# Install system dependencies and PHP extensions
RUN apt-get update && apt-get install -y \
    libfreetype6-dev \
    libjpeg62-turbo-dev \
    libpng-dev \
    libpng-dev \
    libwebp-dev \
    libtidy-dev \
    libzip-dev \
    mariadb-client \
    git \
    && rm -rf /var/lib/apt/lists/* \
    && docker-php-ext-configure gd --with-freetype --with-jpeg --with-webp \
    && docker-php-ext-install -j$(nproc) mysqli pdo pdo_mysql gd tidy zip \
    && a2enmod rewrite

RUN echo "[client]\nssl-verify-server-cert=FALSE" > /etc/mysql/conf.d/no-ssl-verify.cnf
RUN echo "[client]\nssl-verify-server-cert=FALSE" > /etc/mysql/conf.d/no-ssl-verify.cnf

# Set the working directory inside the container
WORKDIR /var/www/html

# Install Composer
# RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

# COPY composer.json ./
# COPY composer.lock ./

# RUN /usr/local/bin/composer install

