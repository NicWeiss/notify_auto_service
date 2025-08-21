FROM debian:10.8

RUN rm /etc/apt/sources.list
RUN touch /etc/apt/sources.list

RUN echo "deb http://archive.debian.org/debian buster main contrib non-free" >> /etc/apt/sources.list
RUN echo "deb http://archive.debian.org/debian-security buster/updates main contrib non-free" >> /etc/apt/sources.list

RUN cat /etc/apt/sources.list
RUN apt update && \
    apt upgrade -y && \
    apt install -y postfix rsyslog opendkim opendkim-tools procps host

# RUN service rsyslog restart
RUN rm /etc/postfix/main.cf /etc/postfix/master.cf
COPY ./docker/postfix/config/main.cf /etc/postfix/main.cf
COPY ./docker/postfix/config/master.cf /etc/postfix/master.cf
RUN cp -f /etc/services /var/spool/postfix/etc/services

RUN hostname --fqdn > /etc/mailname

RUN rm /etc/opendkim.conf
COPY ./docker/postfix/config/opendkim.conf /etc/opendkim.conf

RUN rm /etc/default/opendkim
COPY ./docker/postfix/config/opendkim /etc/default/opendkim

RUN mkdir /etc/postfix/dkim/
COPY ./docker/postfix/key/mail.private /etc/postfix/dkim/mail.private
COPY ./docker/postfix/config/keytable /etc/postfix/dkim/keytable
COPY ./docker/postfix/config/signingtable /etc/postfix/dkim/signingtable
RUN chmod 600 /etc/postfix/dkim/mail.private
RUN chown -R opendkim /etc/postfix/dkim/

RUN service opendkim restart
RUN service postfix restart

RUN touch /var/log/syslog

RUN touch /run.sh
RUN echo 'echo "nameserver 8.8.8.8" >> /etc/resolv.conf' >> /run.sh
RUN echo 'cp /etc/resolv.conf /var/spool/postfix/etc/resolv.conf' >> /run.sh
RUN echo "service opendkim restart" >> /run.sh
RUN echo "postfix start-fg &" >> /run.sh
RUN echo "service rsyslog restart &" >> /run.sh
RUN echo 'while [[ $(service rsyslog status | grep Failed) -ne "" ]]; do echo "Wait logs"; sleep 2; done' >> /run.sh
RUN echo 'while [[ $(service rsyslog status | grep Failed) -ne "" ]]; do echo "Wait logs"; sleep 2; done'
RUN echo "tail -f /var/log/syslog | grep postfix" >> /run.sh
RUN chmod +x /run.sh


CMD ["bash", "/run.sh"]
