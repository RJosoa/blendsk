# Étape 1 : Image de base avec PHP et Apache
FROM php:8.2-apache

# Étape 2 : Installation des extensions PHP nécessaires
RUN apt-get update && apt-get install -y --no-install-recommends \
    libzip-dev unzip git libicu-dev libonig-dev libpq-dev \
    && docker-php-ext-install zip intl pdo pdo_mysql opcache \
    && apt-get clean && rm -rf /var/lib/apt/lists/*

# Étape 3 : Activation du module Apache rewrite
RUN a2enmod rewrite

# Étape 4 : Installation de Composer depuis une image multi-stage
COPY --from=composer:2.6 /usr/bin/composer /usr/bin/composer

# Étape 5 : Définition du répertoire de travail
WORKDIR /var/www

# Étape 6 : Copie du projet Symfony dans le conteneur (avec .dockerignore)
COPY . .

# Étape 7 : Installation des dépendances Symfony en mode production
RUN composer install --no-dev --optimize-autoloader

# Étape 8 : Attribution des droits pour Apache et Symfony
RUN chown -R www-data:www-data /var/www/html/var /var/www/html/public

# Étape 9 : Exposer le port HTTP par défaut d'Apache
EXPOSE 80

# Étape 10 : Lancer Apache en mode premier plan
CMD ["apache2-foreground"]
