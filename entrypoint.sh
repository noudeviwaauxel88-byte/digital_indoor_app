#!/bin/sh

# Mise en cache de la configuration, des routes, des vues et événements
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan event:cache

# Exécution automatique des migrations sur la base de données
php artisan migrate --force

# Création du lien symbolique pour le stockage d'images/documents
php artisan storage:link || true

# Lancer le Queue Worker en arrière-plan (gratuit, dans le même conteneur)
php artisan queue:work --tries=3 --timeout=90 &

# Démarrage du service PHP-FPM en arrière-plan
php-fpm -D

# Démarrage du serveur web Nginx au premier plan
nginx -g "daemon off;"