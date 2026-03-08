FROM php:7.4-apache

RUN a2enmod rewrite

# COPY /beta /usr/src/myapp
# WORKDIR /usr/src/myapp
# CMD [ "php", "./index.php" ]