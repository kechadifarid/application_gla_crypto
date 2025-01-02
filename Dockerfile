# Étape 1 : Utiliser une image PHP avec Apache
FROM php:8.1-apache

# Étape 2 : Installer les dépendances nécessaires
RUN apt-get update && apt-get install -y \
    libzip-dev \
    unzip \
    && docker-php-ext-install zip \
    && pecl install xdebug \
    && docker-php-ext-enable xdebug

# Étape 3 : Configurer Xdebug
RUN echo "zend_extension=xdebug.so" >> /usr/local/etc/php/php.ini
RUN echo "xdebug.mode=coverage" >> /usr/local/etc/php/php.ini
RUN echo "xdebug.start_with_request=yes" >> /usr/local/etc/php/php.ini
RUN echo "xdebug.discover_client_host=true" >> /usr/local/etc/php/php.ini

# Étape 4 : Installer Composer (si nécessaire)
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Étape 5 : Copier les fichiers de l'application dans le conteneur
COPY . /var/www/html/

# Étape 6 : Définir les permissions
RUN chown -R www-data:www-data /var/www/html

# Étape 7 : Exposer le port
EXPOSE 80
