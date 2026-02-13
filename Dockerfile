# Usamos la imagen oficial de PHP con Apache
FROM php:8.2-apache

# Instalamos extensiones para PDO y SQLite
RUN apt-get update && apt-get install -y \
    libsqlite3-dev \
    && docker-php-ext-install pdo pdo_sqlite

# 1. SOPORTE PARA MAYÚSCULAS Y PHP
# Configuramos Apache para que reconozca .php y .PHP por igual
RUN echo "AddType application/x-httpd-php .php .PHP" >> /etc/apache2/apache2.conf && \
    echo "<FilesMatch \"\.(php|PHP)$\">" > /etc/apache2/conf-available/php-handler.conf && \
    echo "  SetHandler application/x-httpd-php" >> /etc/apache2/conf-available/php-handler.conf && \
    echo "</FilesMatch>" >> /etc/apache2/conf-available/php-handler.conf && \
    a2enconf php-handler

# 2. PERMISOS DE REESCRITURA
RUN a2enmod rewrite

# 3. COPIAR PROYECTO
COPY . /var/www/html/

# 4. CONFIGURACIÓN DE DIRECTORIOS Y PERMISOS
# Creamos las carpetas necesarias por si no existen en el repo
RUN mkdir -p /var/www/html/db \
    mkdir -p /var/www/html/image/logo \
    mkdir -p /var/www/html/css

# Ajustamos permisos para que Apache (www-data) pueda escribir:
# - En la carpeta 'db' (para SQLite)
# - En la carpeta 'image' (por si subes posters)
RUN chown -R www-data:www-data /var/www/html && \
    chmod -R 775 /var/www/html/db && \
    chmod -R 775 /var/www/html/image

# 5. CONFIGURACIÓN ESPECÍFICA DE ACCESO
RUN echo "<Directory /var/www/html>" >> /etc/apache2/apache2.conf && \
    echo "  AllowOverride All" >> /etc/apache2/apache2.conf && \
    echo "  Require all granted" >> /etc/apache2/apache2.conf && \
    echo "</Directory>" >> /etc/apache2/apache2.conf

EXPOSE 80