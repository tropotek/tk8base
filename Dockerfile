FROM dunglas/frankenphp:latest

# Set the working directory inside the container
WORKDIR /app

# Install requred apt packages
RUN apt-get update && \
    apt-get install -y --no-install-recommends \
    libzip-dev \
    libjpeg-dev \
    libpng-dev \
    libfreetype6-dev \
    libjpeg62-turbo-dev \
    libicu-dev \
    libxml2-dev \
    unzip \
    vim-nox \
    git \
    openssh-client \
    mariadb-client

# Install required PHP extensions for Moodle
RUN install-php-extensions \
    curl \
    date \
    intl \
    json \
    mbstring \
    pcntl \
    mysqli \
    pdo_mysql \
    gd \
    tidy \
    sockets \
    zip

# Setup .bashrc
RUN echo 'alias l="ls -lah --color=auto"' >> ~/.bashrc

# Install Composer
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

# Clean apt cache in one layer
RUN apt-get clean && rm -rf /var/lib/apt/lists/*

#COPY . .
