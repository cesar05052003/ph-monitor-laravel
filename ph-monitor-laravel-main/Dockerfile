# --- ETAPA 1: BUILD (usa la imagen oficial de Composer con PHP 8.2) ---
FROM composer:2 AS build

# Definimos el directorio de trabajo
WORKDIR /app

# Copiamos todo el proyecto (incluyendo composer.json, artisan, etc.)
COPY . .

# Instalamos dependencias con Composer (sin dev y optimizando autoloader)
RUN composer install --no-dev --optimize-autoloader

# --- ETAPA 2: PRODUCCIÓN (PHP 8.2) ---
FROM php:8.2-cli

# Instalamos extensiones necesarias para Laravel
RUN apt-get update && apt-get install -y \
        libzip-dev \
        unzip \
        libonig-dev \
        libxml2-dev \
    && docker-php-ext-install pdo pdo_mysql zip

# Definimos el directorio de trabajo
WORKDIR /app

# Copiamos la aplicación ya construida
COPY --from=build /app /app

# Exponemos el puerto que usará Laravel
EXPOSE 10000

# Comando de arranque: ejecuta migraciones y luego inicia el servidor
# Las variables de entorno (APP_KEY, DB_*, etc.) deben estar definidas en Render
CMD php artisan migrate --force && \
    php artisan serve --host=0.0.0.0 --port=10000





