# Imagen oficial de PHP con Apache
FROM php:8.2-apache

# Extensiones necesarias: pdo (base) + curl (para conectar a Turso)
RUN apt-get update && apt-get install -y \
    libcurl4-openssl-dev \
    && docker-php-ext-install pdo curl \
    && apt-get clean && rm -rf /var/lib/apt/lists/*

# Soporte para .php y .PHP en mayúsculas
RUN echo "AddType application/x-httpd-php .php .PHP" >> /etc/apache2/apache2.conf && \
    echo "<FilesMatch \"\.(php|PHP)$\">" > /etc/apache2/conf-available/php-handler.conf && \
    echo "  SetHandler application/x-httpd-php" >> /etc/apache2/conf-available/php-handler.conf && \
    echo "</FilesMatch>" >> /etc/apache2/conf-available/php-handler.conf && \
    a2enconf php-handler

# Módulo rewrite
RUN a2enmod rewrite

# Copiar proyecto
COPY . /var/www/html/

# Carpetas de assets (ya no se necesita /db porque usamos Turso)
RUN mkdir -p /var/www/html/image/logo \
    && mkdir -p /var/www/html/css

# Permisos
RUN chown -R www-data:www-data /var/www/html && \
    chmod -R 755 /var/www/html

# Acceso a directorios
RUN echo "<Directory /var/www/html>" >> /etc/apache2/apache2.conf && \
    echo "  AllowOverride All" >> /etc/apache2/apache2.conf && \
    echo "  Require all granted" >> /etc/apache2/apache2.conf && \
    echo "</Directory>" >> /etc/apache2/apache2.conf

EXPOSE 80
