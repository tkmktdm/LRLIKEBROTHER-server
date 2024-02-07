FROM php:8.3.2-fpm

COPY php.ini /usr/local/etc/php/

# for vite server
EXPOSE 5173

RUN set -x \
  && apt-get update \
  && apt-get install -y git libicu-dev libpng-dev libzip-dev libonig-dev wget zip unzip vim \
  && git clone https://github.com/phpredis/phpredis.git /usr/src/php/ext/redis \
  && docker-php-ext-install bcmath exif gd intl pdo_mysql sockets zip opcache redis

RUN pecl install xdebug && docker-php-ext-enable xdebug

# Composer install
ENV COMPOSER_ALLOW_SUPERUSER 1
ENV COMPOSER_HOME /composer
ENV PATH $PATH:/composer/vendor/bin
COPY --from=composer:2.4 /usr/bin/composer /usr/bin/composer

# Install Node and npm
ENV NODE_VERSION=20.11.0
RUN curl -o- https://raw.githubusercontent.com/creationix/nvm/v0.34.0/install.sh | bash
ENV NVM_DIR=/root/.nvm
RUN . "$NVM_DIR/nvm.sh" && nvm install ${NODE_VERSION}
RUN . "$NVM_DIR/nvm.sh" && nvm use v${NODE_VERSION}
RUN . "$NVM_DIR/nvm.sh" && nvm alias default v${NODE_VERSION}
ENV PATH="/root/.nvm/versions/node/v${NODE_VERSION}/bin/:${PATH}"

# WORKDIR /var/www

# ADD . /server/storage

# RUN chown -R www-data:www-data /server/storage

WORKDIR /app