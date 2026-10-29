FROM php:8.2-apache

RUN apt-get update \
    && apt-get install -y --no-install-recommends \
        locales \
        tzdata \
    && sed -i 's/# ja_JP.UTF-8 UTF-8/ja_JP.UTF-8 UTF-8/' /etc/locale.gen \
    && locale-gen \
    && docker-php-ext-install mysqli pdo_mysql \
    && a2enmod rewrite \
    && rm -rf /var/lib/apt/lists[]

ENV LANG=ja_JP.UTF-8
ENV LC_ALL=ja_JP.UTF-8
ENV TZ=Asia/Tokyo

RUN printf '%s\n' \
    'ServerTokens Prod' \
    'ServerSignature Off' \
    > /etc/apache2/conf-available/security-custom.conf \
    && a2enconf security-custom

RUN printf '%s\n' \
    'LogFormat "%h %l %u %t \\"%r\\" %>s %b" common' \
    'CustomLog /proc/self/fd/1 common' \
    'ErrorLog /proc/self/fd/2' \
    > /etc/apache2/conf-available/logging-custom.conf \
    && a2enconf logging-custom

WORKDIR /var/www/html