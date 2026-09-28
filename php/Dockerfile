FROM php:8.3-fpm

# Instalar y habilitar las extensiones PDO requeridas para la conexión con MySQL
RUN docker-php-ext-install pdo pdo_mysql

# Directorio de trabajo estándar para alojar los scripts PHP
WORKDIR /var/www/html
