# --- ETAPA 1: BUILD (usa la imagen oficial de Composer con PHP 8.2) ---
FROM composer:2 as build

# 1. Definimos el directorio de trabajo
WORKDIR /app

# 2. Copiamos TODO el proyecto al contenedor (incluyendo artisan, composer.json, etc.)
COPY . .

# 3. Instalamos dependencias con Composer (sin dev y optimizando el autoloader)
RUN composer install --no-dev --optimize-autoloader

# 4. Generamos APP_KEY y ejecutamos migraciones
RUN php artisan key:generate \
    && php artisan migrate --force

# --- ETAPA 2: PRODUCCIÓN (PHP Runtime 8.2) ---
FROM php:8.2-cli

# 1. Instalamos extensiones necesarias para Laravel
RUN apt-get update && apt-get install -y \
        libzip-dev \
        unzip \
        libonig-dev \
        libxml2-dev \
    && docker-php-ext-install pdo pdo_mysql zip

# 2. Definimos el directorio de trabajo
WORKDIR /app

# 3. Copiamos todo desde la etapa de build
COPY --from=build /app /app

# 4. Exponemos el puerto que usará el servidor integrado de Laravel
EXPOSE 10000

# 5. Iniciamos el servidor integrado de Laravel
CMD ["php", "artisan", "serve", "--host=0.0.0.0", "--port=10000"]


