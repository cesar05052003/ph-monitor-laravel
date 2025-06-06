# --- ETAPA 1: BUILD (usa la imagen oficial de Composer) ---
FROM composer:2 AS build

# Directorio de trabajo
WORKDIR /app

# Copia solo composer.json y composer.lock primero, para instalar dependencias
COPY composer.json composer.lock ./

# Instala dependencias con Composer sin dev y optimizando el autoloader
RUN composer install --no-dev --optimize-autoloader

# Copia el resto del código de la aplicación
COPY . .

# Genera clave de aplicación y corre migraciones
RUN php artisan key:generate \
    && php artisan migrate --force

# --- ETAPA 2: PRODUCCIÓN (PHP Runtime) ---
FROM php:8.1-cli

# Instala extensiones necesarias en runtime
RUN apt-get update && apt-get install -y \
        libzip-dev \
        unzip \
        libonig-dev \
        libxml2-dev \
    && docker-php-ext-install pdo pdo_mysql zip

WORKDIR /app

# Copia todo desde la etapa de build
COPY --from=build /app /app

# Expone el puerto que usa el servidor Laravel
EXPOSE 10000

# Comando para ejecutar la aplicación
CMD ["php", "artisan", "serve", "--host=0.0.0.0", "--port=10000"]

