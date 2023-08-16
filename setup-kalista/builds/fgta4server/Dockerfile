FROM php:8.1-apache

WORKDIR /var/www/html/fgta4

ARG DEBIAN_FRONTEND=noninteractive

ENV PUPPETEER_SKIP_CHROMIUM_DOWNLOAD=true
ENV NODE_PATH=/usr/lib/node_modules


RUN echo "Asia/Jakarta" > /etc/timezone && dpkg-reconfigure -f noninteractive tzdata


ADD https://github.com/mlocati/docker-php-extension-installer/releases/latest/download/install-php-extensions /usr/local/bin/
RUN chmod +x /usr/local/bin/install-php-extensions 


RUN apt-get update && apt-get install -y mcrypt
RUN apt-get install -y nano inetutils-ping netcat
RUN apt-get install -y curl dirmngr apt-transport-https lsb-release ca-certificates

RUN curl -sL https://deb.nodesource.com/setup_12.x | bash -


RUN apt-get install -y nodejs
RUN npm install -g rimraf
RUN npm install -g mariadb
RUN npm install -g nodemon


RUN install-php-extensions pdo_dblib
RUN install-php-extensions pdo_firebird
RUN install-php-extensions pdo_mysql
RUN install-php-extensions xdebug
RUN install-php-extensions gd
RUN install-php-extensions phar
RUN install-php-extensions imap


COPY docker-php-ext-xdebug.ini /usr/local/etc/php/conf.d 
COPY 000-default.conf /etc/apache2/sites-available
COPY .bashrc /root


SHELL ["/bin/bash", "-c"]

EXPOSE 80






# PS1='\[\033[1;36m\]\u\[\033[1;31m\]@\[\033[1;32m\]\h:\[\033[0m\]\w\[\033[0m\]\$\[\033[0m\] '

