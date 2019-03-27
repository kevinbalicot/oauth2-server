FROM php:7.1-apache

EXPOSE 8080

ENV APACHE_DOCUMENT_ROOT /var/www/html/public/

RUN apt-get update -y && apt-get install -y wget git zip unzip
RUN docker-php-ext-install pdo pdo_mysql
RUN a2enmod rewrite

COPY . /var/www/html/
WORKDIR /var/www/html

RUN sed -ri -e 's!/var/www/html!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/sites-available/*.conf
RUN sed -ri -e 's!/var/www/!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/apache2.conf /etc/apache2/conf-available/*.conf

RUN cd /var/www/html/ && wget https://getcomposer.org/composer.phar
RUN cd /var/www/html/ && php composer.phar install
