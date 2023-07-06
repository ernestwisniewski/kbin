# syntax=docker/dockerfile:1.4
FROM php:8.2-fpm-alpine as base

# Install php extensions, by docker-php-extension-installer
COPY --from=mlocati/php-extension-installer /usr/bin/install-php-extensions /usr/local/bin/
RUN install-php-extensions amqp pgsql pdo_pgsql gd curl simplexml dom xml redis intl opcache apcu

# Install composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Install caddy
RUN apk update && apk add caddy acl fcgi

# **Environment variables**
ARG UID=1000
ENV KBIN_HOME=/var/www/kbin \
    USER=kbin \
    GROUP=www-data

# Create user
RUN adduser -u $UID -D -g "" $USER $GROUP

# Create path
RUN mkdir -p $KBIN_HOME && \
    chown -R $USER:$GROUP $KBIN_HOME
WORKDIR $KBIN_HOME

# PHP configuration (Requires these configuration before "composer install" )
RUN cp "$PHP_INI_DIR/php.ini-production" "$PHP_INI_DIR/php.ini"
COPY --link docker/v2/php/conf.d/app.ini $PHP_INI_DIR/conf.d/app.ini
COPY --link docker/v2/php/conf.d/app.prod.ini $PHP_INI_DIR/conf.d/app.prod.or.dev.ini
COPY --link docker/v2/php/conf.d/app.prod.ini $PHP_INI_DIR/conf.d/app.ini-production
COPY --link docker/v2/php/conf.d/app.dev.ini $PHP_INI_DIR/conf.d/app.ini-development
COPY --link docker/v2/php/php-fpm.d/zz-docker.conf /usr/local/etc/php-fpm.d/zz-docker.conf
RUN chown -R $USER:$GROUP $PHP_INI_DIR
RUN chown -R $USER:$GROUP /usr/local/etc/php-fpm.d

####################

FROM caddy:2.7-builder-alpine AS builder-caddy

# Build Caddy with the Mercure and Vulcain modules
RUN xcaddy build v2.6.4 \
	--with github.com/dunglas/mercure/caddy \
	--with github.com/dunglas/vulcain/caddy

####################

FROM base as builder-composer

# Composer: install package
COPY composer.* $KBIN_HOME
COPY symfony.lock $KBIN_HOME
ENV COMPOSER_ALLOW_SUPERUSER=1
RUN composer install --prefer-dist --no-dev --no-autoloader --no-scripts --no-progress

# Copy repository
COPY --link ./ $KBIN_HOME
RUN cp .env.example .env

# Dump-autoload and run post-install script
RUN composer dump-autoload --classmap-authoritative --no-dev
RUN composer run-script --no-dev post-install-cmd && \
    chmod +x bin/console && sync

####################

FROM node:alpine as builder-yarn

# Setup environment
ENV KBIN_HOME=/var/www/kbin
RUN mkdir -p $KBIN_HOME
WORKDIR $KBIN_HOME

# Copy required files
COPY package.json $KBIN_HOME
COPY package-lock.json $KBIN_HOME
COPY yarn.lock $KBIN_HOME
COPY --from=builder-composer --link $KBIN_HOME/vendor $KBIN_HOME/vendor

# Yarn: install package
RUN yarn install --lock-frozen

# Yarn: build
COPY --link ./ $KBIN_HOME
RUN yarn build --mode production

####################

FROM base as runner

COPY --link ./ $KBIN_HOME
RUN cp .env.example .env

COPY --from=builder-caddy --link /usr/bin/caddy /usr/sbin/caddy
COPY --from=builder-composer --chown=$USER:$GROUP $KBIN_HOME/vendor $KBIN_HOME/vendor
COPY --from=builder-yarn --chown=$USER:$GROUP $KBIN_HOME/public $KBIN_HOME/public

COPY --link docker/v2/caddy/Caddyfile /etc/caddy/Caddyfile
COPY --chmod=755 --link docker/v2/docker-entrypoint ./

RUN mkdir -p public/media && \
    chmod 777 public/media

RUN chown -R $USER:$GROUP $KBIN_HOME

# Switch user
USER $USER:$GROUP

ENTRYPOINT ["./docker-entrypoint"]

# Expose port 2019 for caddy metric
EXPOSE 2019/tcp
