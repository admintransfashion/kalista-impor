FROM php:8.1-apache

WORKDIR /var/www/html/fgta5

ARG DEBIAN_FRONTEND=noninteractive

RUN echo "Asia/Jakarta" > /etc/timezone && dpkg-reconfigure -f noninteractive tzdata


ADD https://github.com/mlocati/docker-php-extension-installer/releases/latest/download/install-php-extensions /usr/local/bin/
RUN chmod +x /usr/local/bin/install-php-extensions 


RUN apt-get update && apt-get install -y mcrypt
RUN apt-get install -y nano inetutils-ping netcat
#RUN apt-get install libjpeg-dev
#RUN apt-get install libfreetype6-dev

RUN install-php-extensions pdo_dblib
RUN install-php-extensions pdo_firebird
RUN install-php-extensions pdo_mysql
RUN install-php-extensions xdebug
RUN install-php-extensions gd
RUN install-php-extensions phar


COPY docker-php-ext-xdebug.ini /usr/local/etc/php/conf.d 
COPY 000-default.conf /etc/apache2/sites-available
COPY .bashrc /root
COPY .bashrc /etc/profile.d/bash_profile.sh


RUN a2enmod rewrite


RUN chown -R www-data:www-data /var/www
USER www-data:www-data

SHELL ["/bin/bash", "-c"]

EXPOSE 80



