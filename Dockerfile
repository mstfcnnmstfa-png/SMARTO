FROM php:8.2-apache
RUN a2enmod rewrite
RUN docker-php-ext-install pdo_sqlite
COPY . /var/www/html/
RUN chown -R www-data:www-data /var/www/html && chmod -R 755 /var/www/html && chmod -R 777 /var/www/html/NAMERO
ENV APACHE_DOCUMENT_ROOT /var/www/html
RUN sed -ri -e 's!/var/www/html!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/sites-available/*.conf
EXPOSE 8080
RUN sed -i 's/80/8080/g' /etc/apache2/ports.conf
CMD ["apache2-foreground"]
