FROM nginx:1.21-alpine

ARG USER_ID
ARG GROUP_ID

RUN apk add --update  \
    git \
    nano \
    wget \
    curl \
    zip \
    tcpdump \
    ca-certificates \
    nss-tools \
    openssl \
    rsync \
    g++ make gcc automake autoconf  \
    && rm -rf /var/cache/apk/*

RUN curl -JLO "https://dl.filippo.io/mkcert/latest?for=linux/amd64" \
 && chmod +x mkcert-v*-linux-amd64 \
 && cp mkcert-v*-linux-amd64 /usr/local/bin/mkcert
RUN mkdir -p /etc/nginx/ssl/ \
 && mkcert -install \
 && cd /etc/nginx/ssl/ \
 && mkcert user-activity-analysis_nginx "*.user-activity-analysis_nginx" localhost 127.0.0.1 ::1 \
 && mkcert nginx "*.nginx" localhost 127.0.0.1 ::1

RUN mkdir -p /tmp/fwatch \
    && cd /tmp/fwatch \
    && wget https://github.com/emcrisostomo/fswatch/releases/download/1.17.1/fswatch-1.17.1.tar.gz \
    && tar xvf fswatch-1.17.1.tar.gz \
    && cd fswatch-1.17.1 \
    && ./configure && make && make install

RUN rm -rf /etc/nginx/conf.d/default.conf

COPY nginx.conf  /etc/nginx/

RUN addgroup -g $GROUP_ID nginx-user \
 && adduser -G nginx-user -D -u $USER_ID nginx-user \
 && chown nginx-user:nginx-user /tmp \
 && chown -R nginx-user:nginx-user /etc/nginx/conf.d/ \
 && chown -R nginx-user:nginx-user /etc/nginx/ssl/ \
 && chmod g+w,g+s /etc/nginx/conf.d/ \
 && chown -R nginx-user:nginx-user /var/run/ \
 && mkdir -p /var/log/nginx \
 && chown -R nginx-user: /var/log/nginx \
 && chmod g+w,g+s /var/log/nginx

COPY ./entrypoint.sh /
RUN sed -i 's/\r$//' /entrypoint.sh
RUN chmod +x /entrypoint.sh

#USER nginx-user
WORKDIR /var/www

ENTRYPOINT ["/bin/sh", "/entrypoint.sh"]
