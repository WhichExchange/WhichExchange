FROM php:7-apache

WORKDIR /var/www/html

COPY . .

RUN mkdir cache && chown www-data:www-data cache
