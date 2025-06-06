# --- ETAPA 1: BUILD (usa la imagen oficial de Composer con PHP 8.2) ---
FROM composer:2 as build

# Definimos el directorio de trabajo
WORKDIR /app

# Copiamos solo composer.json y composer.lock para aprovechar cache de dependencias
COPY composer.json composer.lock ./

# Instalamos dependencias con Composer (sin dev y optimizando autoloader)
RUN composer install --no-dev --optimize-autoloader

# Copiamos todo el código al contenedor
COPY . .

# Generamos APP_KEY y ejecutamos migraciones
RUN php artisan key:generate \
    && php artisan migrate --force

# --- ETAPA 2: PRODUCCIÓN (imagen con PHP 8.2) ---
FROM php:8.2-cli

# Instalamos extensiones necesarias: pdo_mysql, zip, etc.
RUN apt-get update && apt-get install -y \
        libzip-dev \
        unzip \
        libonig-dev \
        libxml2-dev \
    && docker-php-ext-install pdo pdo_mysql zip

# Directorio de trabajo
WORKDIR /app

# Copiamos todo desde la etapa de build
COPY --from=build /app /app

# Exponemos el puerto que usará Laravel
EXPOSE 10000

# Comando para iniciar el servidor integrado de Laravel
CMD ["php", "artisan", "serve", "--host=0.0.0.0", "--port=10000"]


