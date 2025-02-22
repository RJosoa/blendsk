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

# Définir le répertoire de travail
WORKDIR /var/www

# Copier les fichiers de configuration
COPY composer.json composer.lock ./

# Installer les dépendances en mode production sans les scripts
RUN composer install --prefer-dist --no-dev --optimize-autoloader --no-scripts

# Copier le reste des fichiers du projet
COPY . .

# Copier le fichier de configuration Apache et l'activer
COPY docker/000-custom.conf /etc/apache2/sites-available/000-custom.conf
RUN a2ensite 000-custom.conf && a2dissite 000-default.conf

# Définir les permissions appropriées
RUN chown -R www-data:www-data . && \
    chmod -R 755 . && \
    chmod -R 775 var

EXPOSE 80

CMD ["apache2-foreground"]
