# --- Étape 1 : Build du Front-end (Vite / Alpine / Assets) ---
FROM node:20 AS frontend
WORKDIR /app
COPY package*.json ./
RUN npm install
COPY . .
RUN npm run build

# --- Étape 2 : Image de Production PHP + Nginx ---
FROM php:8.2-fpm

# Installation des dépendances système et des extensions PHP (MySQL, PostgreSQL, GD, ZIP)
RUN apt-get update && apt-get install -y \
    git \
    curl \
    libpq-dev \
    libzip-dev \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    zip \
    unzip \
    nginx \
    && docker-php-ext-install pdo pdo_mysql pdo_pgsql zip mbstring exif pcntl bcmath gd

# Installation de Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Répertoire de travail
WORKDIR /var/www

# Copie du code source complet
COPY . .

# Copie des assets CSS/JS compilés depuis la première étape frontend
COPY --from=frontend /app/public/build ./public/build

# Installation des dépendances PHP optimisées pour la production
RUN composer install --no-dev --optimize-autoloader --no-interaction

# Attribution des permissions pour le stockage et le cache
RUN chown -R www-data:www-data /var/www/storage /var/www/bootstrap/cache \
    && chmod -R 775 /var/www/storage /var/www/bootstrap/cache

# Copie et configuration du script de démarrage
COPY entrypoint.sh /usr/local/bin/entrypoint.sh
RUN chmod +x /usr/local/bin/entrypoint.sh

EXPOSE 80

ENTRYPOINT ["/usr/local/bin/entrypoint.sh"]