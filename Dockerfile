# 1. Imagen base limpia
FROM php:8.2-apache

# 2. Instalamos solo lo estrictamente necesario para Turso/SQLite
RUN apt-get update && apt-get install -y \
    libsqlite3-dev \
    libcurl4-openssl-dev \
    && docker-php-ext-install pdo pdo_sqlite curl \
    && apt-get clean && rm -rf /var/lib/apt/lists/*

# 3. Habilitamos el módulo rewrite
RUN a2enmod rewrite

# 4. Copiamos los archivos (esto ya incluye tu .htaccess si tienes uno)
COPY . /var/www/html/

# 5. Permisos de carpetas
RUN chown -R www-data:www-data /var/www/html && chmod -R 755 /var/www/html

# 6. IMPORTANTE: No editamos puertos ni MPM aquí. 
# Railway detecta el puerto 80 por defecto si no le decimos nada.
# Solo nos aseguramos de que Apache corra en primer plano.
CMD ["apache2-foreground"]
