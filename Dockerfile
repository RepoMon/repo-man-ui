FROM php:7-fpm

MAINTAINER Tim Rodger <tim.rodger@gmail.com>

EXPOSE 9000

RUN apt-get update -qq && \
    apt-get install -y \
    curl \
    libicu-dev \
    zip \
    unzip \
    git

# install bcmath and mbstring for videlalvaro/php-amqplib
RUN docker-php-ext-install bcmath mbstring

RUN curl -sS https://getcomposer.org/installer | php \
  && mv composer.phar /usr/bin/composer

CMD ["/home/app/run.sh"]

# Move application files into place
COPY src/ /home/app/

WORKDIR /home/app

# remove any development cruft
# RUN rm -rf /home/app/vendor/*

# Install dependencies
RUN composer install --prefer-dist && \
    apt-get clean

RUN chmod +x  /home/app/run.sh

USER root

