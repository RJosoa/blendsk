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

# Installation de Composer (copié depuis l'image officielle)
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Définir le répertoire de travail
WORKDIR /var/www/html

# Configure Git safe directory pour éviter l'erreur d'ownership
RUN git config --global --add safe.directory /var/www/html

# Copier les fichiers de configuration de Composer pour profiter du cache
COPY composer.json composer.lock ./

# Si le fichier .env n'existe pas, le créer à partir de .env.example s'il existe ou générer un .env minimal
RUN if [ ! -f .env ]; then \
      if [ -f .env.example ]; then cp .env.example .env; else echo "APP_ENV=prod" > .env; fi; \
    fi

# Installer les dépendances sans exécuter les scripts et sans dépendances de développement
RUN composer install --no-scripts --no-dev --no-autoloader

# Copier le reste de l'application
COPY . .

# Générer l'autoloader optimisé et exécuter les scripts post-installation (ex : cache:clear)
RUN composer dump-autoload --optimize && \
    composer run-script post-install-cmd --no-dev

# Configurer la racine des documents d'Apache (doit pointer vers le dossier public)
ENV APACHE_DOCUMENT_ROOT /var/www/html/public

RUN sed -ri -e 's!/var/www/html!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/sites-available/*.conf \
    && sed -ri -e 's!/var/www/!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/apache2.conf /etc/apache2/conf-available/*.conf

# Définir les permissions sur le projet
RUN mkdir -p var && \
    chown -R www-data:www-data . && \
    chmod -R 755 . && \
    chmod -R 777 var

EXPOSE 80

CMD ["apache2-foreground"]
