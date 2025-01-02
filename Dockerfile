# Utiliser une image de base PHP avec Apache
FROM php:8.1-apache

# Installer les extensions nécessaires
RUN docker-php-ext-install pdo pdo_pgsql

# Copier le contenu de votre projet dans le conteneur
COPY . /var/www/html

# Donner les permissions correctes au répertoire
RUN chown -R www-data:www-data /var/www/html \
    && chmod -R 755 /var/www/html

# Exposer le port 80
EXPOSE 80

# Commande pour démarrer Apache
CMD ["apache2-foreground"]