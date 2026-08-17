# --- Étape 1 : Build du Front-end (Vite / Alpine) ---
FROM node:20 AS frontend
WORKDIR /app
COPY package*.json ./
RUN npm install
COPY . .
RUN npm run build

# --- Étape 2 : Production PHP / Laravel ---
FROM php:8.2-fpm

# Installation des dépendances système et extensions PostgreSQL / ZIP
RUN apt-get update && apt-get install -y \
    git curl libpq-dev libzip-dev zip unzip \
    && docker-php-ext-install pdo pdo_pgsql zip

# Récupération de Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

WORKDIR /var/www

# Copie du code source
COPY . .

# Copie des assets compilés depuis l'étape frontend
COPY --from=frontend /app/public/build ./public/build

# Installation des dépendances PHP sans dépendances dev
RUN composer install --no-dev --optimize-autoloader

# Nettoyage de la configuration et gestion des permissions
RUN php artisan config:clear
RUN chown -R www-data:www-data storage bootstrap/cache

EXPOSE 8080

CMD php artisan serve --host=0.0.0.0 --port=8080