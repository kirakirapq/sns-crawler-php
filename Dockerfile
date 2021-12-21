FROM php:8.0-apache-buster

ARG DEPLOY_ENV=develop
ARG APP_DIR=crawler-php
ENV APP_DIR ${APP_DIR}
ENV LARAVEL_ROOT /var/www/html/${APP_DIR}
ENV APACHE_DOCUMENT_ROOT ${LARAVEL_ROOT}/public

# apache server confing file
COPY 000-default.conf /etc/apache2/sites-available/000-default.conf
COPY apache2.conf /etc/apache2/apache2.conf

RUN apt-get update \
  && apt-get install -y zlib1g-dev libzip-dev libpq-dev unzip git vim curl \
  && docker-php-ext-install pdo_mysql zip

# sorce and composer file
COPY --from=composer /usr/bin/composer /usr/bin/composer
COPY ./${APP_DIR} ${LARAVEL_ROOT}
WORKDIR ${LARAVEL_ROOT}
RUN composer install
RUN mv ${DEPLOY_ENV}.env .env

COPY --chown=www-data:www-data ./entrypoint.sh /var/www/
RUN chmod 755 /var/www/entrypoint.sh

RUN mv /etc/apache2/mods-available/rewrite.load /etc/apache2/mods-enabled
RUN /bin/sh -c a2enmod rewrite

RUN chown -R www-data:www-data /var/www
RUN chmod -R 777 ${LARAVEL_ROOT}/storage

USER www-data

EXPOSE 80
CMD ["sh", "/var/www/entrypoint.sh"]
