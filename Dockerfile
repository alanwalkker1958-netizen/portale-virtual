# Usamos la imagen oficial de PHP con Apache
FROM php:8.2-apache

# Habilitar el módulo de reescritura de Apache (mod_rewrite)
RUN a2enmod rewrite

# Copiar todos los archivos de tu proyecto al directorio web de Apache
COPY . /var/www/html/

# Ajustar los permisos para que Apache funcione correctamente
RUN mkdir -p /var/www/html/actions /var/www/html/inicio/b-14/actions && \
    chown -R www-data:www-data /var/www/html && \
    chmod -R 777 /var/www/html

# Indicar que el contenedor escuchará en el puerto 80
EXPOSE 80
