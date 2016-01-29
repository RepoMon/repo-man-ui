FROM php:7-fpm

MAINTAINER Tim Rodger <tim.rodger@gmail.com>

EXPOSE 80

RUN apt-get update -qq && \
    apt-get install -y \
    curl \
    libicu-dev \
    zip \
    unzip \
    git \
    nginx

# install bcmath and mbstring for videlalvaro/php-amqplib
RUN docker-php-ext-install bcmath mbstring

RUN curl -sS https://getcomposer.org/installer | php \
  && mv composer.phar /usr/bin/composer

CMD ["/home/app/run.sh"]

# Move application files into place
COPY src/ /home/app/

COPY build/nginx.conf /etc/nginx/
COPY build/php-fpm.conf /etc/php/fpm/

# remove any development cruft
RUN rm -rf /home/app/vendor/*

RUN chmod +x  /home/app/run.sh

WORKDIR /home/app

# Install dependencies
RUN composer install --prefer-dist && \
    apt-get clean

# WORKDIR /home/app/public

USER root

