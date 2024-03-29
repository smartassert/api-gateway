FROM php:8.3-fpm

WORKDIR /app

ARG APP_ENV=prod
ARG DATABASE_URL=postgresql://database_user:database_password@0.0.0.0:5432/database_name?serverVersion=12&charset=utf8
ARG AUTHENTICATION_BASE_URL=https://users.example.com
ARG SOURCES_BASE_URL=https://sources.example.com
ARG JOB_COORDINATOR_BASE_URL=https://job-coordinator.example.com

ENV APP_ENV=$APP_ENV
ENV DATABASE_URL=$DATABASE_URL
ENV AUTHENTICATION_BASE_URL=$AUTHENTICATION_BASE_URL
ENV SOURCES_BASE_URL=$SOURCES_BASE_URL
ENV JOB_COORDINATOR_BASE_URL=$JOB_COORDINATOR_BASE_URL

COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

RUN apt-get -qq update && apt-get -qq -y install  \
  git \
  libpq-dev \
  libzip-dev \
  supervisor \
  zip \
  && docker-php-ext-install \
  pdo_pgsql \
  zip \
  && apt-get autoremove -y \
  && rm -rf /var/lib/apt/lists/* /tmp/* /var/tmp/*

COPY composer.json /app/
COPY bin/console /app/bin/console
COPY public/index.php public/
COPY src /app/src
COPY config/bundles.php config/services.yaml /app/config/
COPY config/packages/*.yaml /app/config/packages/
COPY config/routes.yaml /app/config/

RUN mkdir -p /app/var/log \
  && chown -R www-data:www-data /app/var/log \
  && echo "APP_SECRET=$(cat /dev/urandom | tr -dc 'a-zA-Z0-9' | fold -w 32 | head -n 1)" > .env \
  && COMPOSER_ALLOW_SUPERUSER=1 composer install --no-dev --no-scripts \
  && rm composer.lock \
  && php bin/console cache:clear
