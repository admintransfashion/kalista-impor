FROM mysql:8.0.30

RUN mkdir /var/database
RUN mkdir /var/database/backup

COPY .bashrc /root
COPY my.cnf /etc/my.cnf

WORKDIR /var/database/backup

EXPOSE 3306