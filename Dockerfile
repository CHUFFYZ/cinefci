# 1. Usamos la imagen oficial de PHP con Apache
FROM php:8.2-apache

# 2. Instalamos extensiones necesarias (pdo para base y pdo_sqlite para Turso local/remoto)
RUN apt-get update && apt-get install -y \
    libcurl4-openssl-dev \
    libsqlite3-dev \
    && docker-php-ext-install pdo pdo_sqlite curl \
    && apt-get clean && rm -rf /var/lib/apt/lists/*

# 3. Habilitamos el módulo rewrite (indispensable)
RUN a2enmod rewrite

# 4. Ajuste para que Apache escuche en el puerto que Railway le asigne ($PORT)
# Esto evita que Apache falle al intentar usar el puerto 80 fijo
RUN sed -i 's/80/${PORT}/g' /etc/apache2/sites-available/000-default.conf /etc/apache2/ports.conf

# 5. Copiamos el proyecto
COPY . /var/www/html/

# 6. Creamos las carpetas de assets y damos permisos de una vez
RUN mkdir -p /var/www/html/image/logo /var/www/html/css \
    && chown -R www-data:www-data /var/www/html \
    && chmod -R 755 /var/www/html

# 7. Configuración simple de directorio (sin duplicar módulos MPM)
RUN echo "<Directory /var/www/html>\n\
    AllowOverride All\n\
    Require all granted\n\
    DirectoryIndex index.php index.html\n\
</Directory>" >> /etc/apache2/apache2.conf

# 8. Comando de inicio oficial
CMD ["apache2-foreground"]
