FROM php:8.2-apache

# Installation des dépendances système et extensions PHP
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

# Copier les fichiers du projet
COPY . .

# Copier le fichier de configuration personnalisé dans Apache
COPY docker/000-custom.conf /etc/apache2/sites-available/000-custom.conf
RUN a2ensite 000-custom.conf && a2dissite 000-default.conf

# Set proper permissions
RUN chown -R www-data:www-data . && \
    chmod -R 755 . && \
    chmod -R 775 var

EXPOSE 80
