FROM php:8.3.16-fpm

ARG USER_ID
ARG GROUP_ID

COPY php.ini /usr/local/etc/php/conf.d/docker-php-config.ini

RUN apt-get update && apt-get install -y \
    zip \
    libzip-dev \
    openssl \
    git \
    unzip \
    locales \
    netcat-traditional \
    locales-all \
    libpq-dev \
    && echo 'alias sf="php bin/console"' >> ~/.bashrc \
    && apt-get clean

RUN docker-php-ext-install zip

# Install Postgre PDO
RUN docker-php-ext-install pdo pdo_pgsql

RUN rm -rf /var/lib/apt/lists/* /tmp/* /var/tmp/*

# add russian locale and add user www-data right for read and write
# https://stackoverflow.com/questions/28405902/how-to-set-the-locale-inside-a-debian-ubuntu-docker-container
RUN sed -i "s/33:33/$USER_ID:$GROUP_ID/g" /etc/passwd && sed -i "s/33/$GROUP_ID/g" /etc/group \
    && echo "en_US.UTF-8 UTF-8" >> /etc/locale.gen \
    && echo "ru_RU.UTF-8 UTF-8" >> /etc/locale.gen \
    && echo "LANG=ru_RU.UTF-8" > /etc/locale.conf \
    && locale-gen \
    && localedef -c -f UTF-8 -i ru_RU ru_RU.UTF-8

RUN mkdir -p /var/log/php/ \
    && chown $USER_ID:$GROUP_ID -R /var/log/php/ \
    && chmod g+rwxs,u+rwx,o+r -R /var/log/php/

ENV LANG ru_RU.UTF-8
ENV LANGUAGE ru_RU:ru
ENV LC_ALL ru_RU.UTF-8

RUN mkdir -p /home/www-data/.composer /home/www-data/.ssh && \
    touch /home/www-data/.ssh/known_hosts && \
    ssh-keyscan -t rsa github.com >> /home/www-data/.ssh/known_hosts && \
    chown -R www-data:www-data /home/www-data && \
    chmod 700 /home/www-data/.ssh && \
    usermod -d /home/www-data www-data

# Install composer
COPY --from=composer:2.5.8 /usr/bin/composer /usr/bin/composer

# Install symfony
RUN curl -sS https://get.symfony.com/cli/installer | bash \
    && ln -s /root/.symfony/bin/symfony /usr/local/bin/symfony

WORKDIR /var/www

USER www-data
