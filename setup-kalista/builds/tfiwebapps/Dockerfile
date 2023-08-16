FROM php:8.0-apache


ARG DEBIAN_FRONTEND=noninteractive

RUN echo "Asia/Jakarta" > /etc/timezone && dpkg-reconfigure -f noninteractive tzdata

ADD https://github.com/mlocati/docker-php-extension-installer/releases/latest/download/install-php-extensions /usr/local/bin/
RUN chmod +x /usr/local/bin/install-php-extensions 


# RUN apt-get update && apt-get install -y mcrypt
# RUN apt-get install -y nano inetutils-ping netcat
RUN install-php-extensions gd
RUN install-php-extensions mysqli
RUN install-php-extensions pdo_mysql
RUN install-php-extensions xdebug

RUN mkdir /var/www/html/tfiweb
RUN mkdir -pv /var/cpanel/php/sessions/ea-php81
RUN chmod 777 /var/cpanel/php/sessions/ea-php81

COPY docker-php-ext-xdebug.ini /usr/local/etc/php/conf.d 
COPY apache2.conf /etc/apache2/apache2.conf
COPY tfiweb.conf /etc/apache2/sites-available/tfiweb.conf

RUN a2dissite 000-default.conf
RUN rm /etc/apache2/sites-available/000-default.conf
RUN a2ensite tfiweb.conf
RUN a2enmod rewrite


COPY .bashrc /root
WORKDIR /var/www/html
