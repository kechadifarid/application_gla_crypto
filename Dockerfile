# Utiliser une image PHP avec Apache
FROM php:8.1-apache

# Installer les extensions PHP et les outils nécessaires
RUN apt-get update && apt-get install -y --no-install-recommends \
    unzip \
    libpq-dev \
    curl \
    git \
    && docker-php-ext-install pdo_pgsql \
    && apt-get clean \
    && rm -rf /var/lib/apt/lists/*

# Installer Composer
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

# Copier le code source dans l'image
COPY . /var/www/html/

# Configurer les permissions
RUN chown -R www-data:www-data /var/www/html && chmod -R 755 /var/www/html

# Réinitialiser les modifications dans les dépendances Git et installer avec Composer
RUN rm -rf /var/www/html/vendor \
    && COMPOSER_ALLOW_SUPERUSER=1 composer install --no-dev --optimize-autoloader --ignore-platform-reqs \
    || (git -C /var/www/html/vendor/theseer/tokenizer reset --hard && git -C /var/www/html/vendor/theseer/tokenizer clean -fd && composer install --no-dev --optimize-autoloader --ignore-platform-reqs)

# Exposer le port utilisé par Apache
EXPOSE 80

# Démarrer le serveur Apache
CMD ["apache2-foreground"]
