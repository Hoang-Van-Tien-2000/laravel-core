FROM php:8.1-apache-bullseye

RUN apt-get update && \
    apt-get install -y \
        libzip-dev \
        zip \
        unzip \
        git \
        curl \
#        openssl \
#        libssl-dev \
#        libpng-dev \
        libjpeg62-turbo-dev \
        libfreetype6-dev \
        sendmail-bin \
        sendmail \
        mailutils && \
    rm -rf /var/lib/apt/lists/*

RUN docker-php-ext-configure gd --with-freetype --with-jpeg
RUN docker-php-ext-install gd mysqli pdo pdo_mysql opcache zip

RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

# Set up Apache configuration
RUN a2enmod rewrite && \
    sed -i 's!/var/www/html!/var/www/html/public!g' /etc/apache2/sites-available/000-default.conf && \
    chown -R www-data:www-data /var/www/html

EXPOSE 80
