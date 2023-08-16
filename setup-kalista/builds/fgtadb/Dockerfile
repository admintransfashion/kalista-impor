FROM mariadb:10.6.8


RUN mkdir /var/setup

COPY *.sql /var/setup/
COPY fgtadbsetup.sh  /var/setup/

#RUN chmod +x /var/setup/fgtadbsetup.sh
#RUN /var/setup/fgtadbsetup.sh
#RUN rm *.sql
#RUN rm fgtadbsetup.sh

EXPOSE 3306