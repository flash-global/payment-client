FROM php:5.6-apache

RUN apt-get update

RUN apt-get install -y php5-xdebug

RUN usermod -u 1000 www-data

RUN a2enmod rewrite
