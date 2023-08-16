FROM php:8.1-apache

WORKDIR /var/www

ARG DEBIAN_FRONTEND=noninteractive

RUN echo "Asia/Jakarta" > /etc/timezone && dpkg-reconfigure -f noninteractive tzdata


ADD https://github.com/mlocati/docker-php-extension-installer/releases/latest/download/install-php-extensions /usr/local/bin/
RUN chmod +x /usr/local/bin/install-php-extensions 


RUN apt-get update && apt-get install -y mcrypt
RUN apt-get install -y nano inetutils-ping netcat
RUN apt-get install libnss3-tools -y

COPY mkcert-v1.4.4-linux-amd64 /usr/local/bin/mkcert
RUN chmod +x /usr/local/bin/mkcert
RUN mkcert -install
RUN mkcert -CAROOT
RUN mkdir /root/ssl

# RUN mkcert  -cert-file /root/ssl/localhost.pem  -key-file /root/ssl/localhost-key.pem localhost
# RUN mkcert  -cert-file /root/ssl/fgta4.localhost.pem  -key-file /root/ssl/fgta4.localhost-key.pem fgta4.localhost
# RUN mkcert  -cert-file /root/ssl/fgta5.localhost.pem  -key-file /root/ssl/fgta5.localhost-key.pem fgta5.localhost
# RUN mkcert  -cert-file /root/ssl/tfiweb.localhost.pem  -key-file /root/ssl/tfiweb.localhost-key.pem tfiweb.localhost

# RUN cp /root/ssl/localhost.pem /etc/ssl/certs/
# RUN cp /root/ssl/*.localhost.pem /etc/ssl/certs/
# RUN cp /root/ssl/localhost-key.pem /etc/ssl/private/
# RUN cp /root/ssl/*.localhost-key.pem /etc/ssl/private/

COPY ssl/localhost.pem /etc/ssl/certs
COPY ssl/fgta4.localhost.pem /etc/ssl/certs
COPY ssl/fgta5.localhost.pem /etc/ssl/certs
COPY ssl/tfiweb.localhost.pem /etc/ssl/certs

COPY ssl/localhost-key.pem /etc/ssl/private
COPY ssl/fgta4.localhost-key.pem /etc/ssl/private
COPY ssl/fgta5.localhost-key.pem /etc/ssl/private
COPY ssl/tfiweb.localhost-key.pem /etc/ssl/private

COPY 000-default-http.conf /etc/apache2/sites-available/000-default-http.conf
COPY 000-default-https.conf /etc/apache2/sites-available/000-default-https.conf


RUN a2dissite 000-default.conf
RUN rm /etc/apache2/sites-available/000-default.conf

RUN a2ensite 000-default-http.conf
RUN a2ensite 000-default-https.conf


COPY .bashrc /root

RUN a2enmod rewrite
RUN a2enmod ssl
RUN a2enmod proxy
RUN a2enmod proxy_http
RUN a2enmod headers

SHELL ["/bin/bash", "-c"]

EXPOSE 80



