# Etapa 1: Construcción
FROM php:8.1-cli AS build

# 1. Instalar dependencias del sistema necesarias para Laravel (pdo_mysql, zip, etc.)
RUN apt-get update && apt-get install -y \
    git \
    unzip \
    libzip-dev \
    libonig-dev \
    libxml2-dev \
    && docker-php-ext-install pdo pdo_mysql zip

# 2. Copiar composer desde la imagen oficial de Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# 3. Definir directorio de trabajo
WORKDIR /var/www/html

# 4. Copiar los archivos de tu proyecto al contenedor
COPY . /var/www/html

# 5. Instalar dependencias de PHP con Composer
RUN composer install --optimize-autoloader --no-dev

# 6. Generar APP_KEY de Laravel (si no la tienes ya en el .env)
RUN php artisan key:generate

# 7. Ejecutar migraciones en construcción (opcional: si quieres que las migraciones se apliquen al build)
#    Si prefieres ejecutarlas en tiempo de ejecución, comenta o elimina esta línea
RUN php artisan migrate --force

# Etapa 2: Producción
FROM php:8.1-cli

# 1. Instalar extensiones necesarias (igual que en build)
RUN apt-get update && apt-get install -y \
    libzip-dev \
    libonig-dev \
    libxml2-dev \
    && docker-php-ext-install pdo pdo_mysql zip

# 2. Definir directorio de trabajo
WORKDIR /var/www/html

# 3. Copiar todo desde la etapa de build
COPY --from=build /var/www/html /var/www/html

# 4. Exponer el puerto donde Laravel servirá (coincide con tu “startCommand”)
EXPOSE 10000

# 5. Iniciar el servidor integrado de Laravel
CMD ["php", "artisan", "serve", "--host=0.0.0.0", "--port=10000"]
