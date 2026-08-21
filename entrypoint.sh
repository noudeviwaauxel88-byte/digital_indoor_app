#!/bin/sh

# Mettre en cache la configuration, les routes et les vues au démarrage
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan event:cache

# Exécuter les migrations en production de manière automatique
php artisan migrate --force

# Démarrer le serveur PHP-FPM en arrière-plan
php-fpm -D

# Démarrer le serveur Nginx au premier plan
nginx -g "daemon off;"