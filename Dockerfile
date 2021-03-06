FROM php:7.3.5-apache

RUN apt-get update \
  && echo 'deb http://packages.dotdeb.org jessie all' >> /etc/apt/sources.list \
  && echo 'deb-src http://packages.dotdeb.org jessie all' >> /etc/apt/sources.list \
  && apt-get install -y gnupg wget \
  && apt-get install -y libzip-dev \
  && wget https://www.dotdeb.org/dotdeb.gpg -O /tmp/dotdeb.gpg \
  && apt-key add /tmp/dotdeb.gpg \
  && apt-get update

RUN docker-php-ext-install mysqli

RUN apt-get install -y zlib1g-dev libpng-dev

RUN docker-php-ext-install pdo_mysql

RUN docker-php-ext-install zip

RUN docker-php-ext-install gd

COPY --from=composer:1.10.16 /usr/bin/composer /usr/bin/composer

RUN a2enmod rewrite
