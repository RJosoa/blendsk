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

# Optionnel : désactiver puis réactiver le site par défaut
# RUN a2dissite 000-default.conf && a2ensite 000-default.conf

# Installation de Composer depuis l'image officielle
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Définir le répertoire de travail
WORKDIR /var/www/html

# Copier l'ensemble des fichiers du projet
COPY . .

# Installation des dépendances PHP et génération de l'autoloader
RUN composer install && composer dump-autoload --optimize

# Configuration de la racine des documents Apache (pointant vers le dossier public)
ENV APACHE_DOCUMENT_ROOT /var/www/html/public
RUN sed -ri -e 's!/var/www/html!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/sites-available/*.conf \
    && sed -ri -e 's!/var/www/!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/apache2.conf /etc/apache2/conf-available/*.conf

# Définir les permissions sur le dossier et créer le répertoire var
RUN mkdir -p var && \
    chown -R www-data:www-data . && \
    chmod -R 755 . && \
    chmod -R 777 var

EXPOSE 80

CMD ["apache2-foreground"]
