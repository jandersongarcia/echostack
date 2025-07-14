FROM php:8.3-apache

# Instala extensões PHP necessárias
RUN apt-get update && apt-get install -y \
    libzip-dev unzip git curl libonig-dev libxml2-dev libpng-dev libjpeg-dev libfreetype6-dev \
    && docker-php-ext-install pdo pdo_mysql zip

# Ativa mod_rewrite
RUN a2enmod rewrite

# Ajusta o DocumentRoot para /var/www/html/app
RUN sed -i 's|DocumentRoot /var/www/html|DocumentRoot /var/www/html/app|g' /etc/apache2/sites-available/000-default.conf

# Permite uso de .htaccess
RUN sed -i 's|AllowOverride None|AllowOverride All|g' /etc/apache2/apache2.conf

# Define permissões e cópia
WORKDIR /var/www/html
COPY . /var/www/html
RUN chown -R www-data:www-data /var/www/html && chmod -R 755 /var/www/html

# Instala Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer
