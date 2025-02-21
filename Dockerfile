FROM php:8.2-apache

RUN apt-get update && apt-get install -y \
    libonig-dev \
    libxml2-dev \
    zip \
    unzip \
    && docker-php-ext-install pdo_mysql

RUN a2enmod rewrite

# Copier l'intégralité du projet dans /var/www (vous pouvez adapter selon votre structure)
WORKDIR /var/www

COPY . .

# Déplacer le contenu du dossier public dans le DocumentRoot d'Apache
RUN cp -R public /var/www/html && \
    chown -R www-data:www-data /var/www/html && \
    chmod -R 777 /var/www/html

EXPOSE 80

CMD ["apache2-foreground"]
