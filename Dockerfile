FROM php:8.2-apache

# Installation des dépendances système et PHP extensions
RUN apt-get update && apt-get install -y \
    libonig-dev \
    libxml2-dev \
    zip \
    unzip \
    git \
    && docker-php-ext-install pdo_mysql \
    && apt-get clean \
    && rm -rf /var/lib/apt/lists/*

# Activation du module rewrite d'Apache
RUN a2enmod rewrite

# Installation de Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Configuration du répertoire de travail
WORKDIR /var/www

# Copie des fichiers du projet
COPY . .

# Set proper permissions
RUN chown -R www-data:www-data . && \
    chmod -R 755 . && \
    chmod -R 777 var


EXPOSE 80
