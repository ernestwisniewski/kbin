FROM php:8.2-fpm-alpine as base

# Install php extensions, by docker-php-extension-installer
COPY --from=mlocati/php-extension-installer /usr/bin/install-php-extensions /usr/local/bin/
RUN install-php-extensions amqp pgsql pdo_pgsql gd curl simplexml dom xml redis intl

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

####################

# Build Caddy with the Mercure and Vulcain modules
FROM caddy:2.7-builder-alpine AS builder-caddy

RUN xcaddy build v2.6.4 \
	--with github.com/dunglas/mercure/caddy \
	--with github.com/dunglas/vulcain/caddy

####################

FROM base as builder-1

# Clone repository
RUN apk update && apk add git
RUN git clone https://codeberg.org/Kbin/kbin-core.git $KBIN_HOME
RUN mkdir -p public/media && \
    chmod 777 public/media && \
    cp .env.example .env

# PHP configuration (need to this before setting up composer)
RUN cp "$PHP_INI_DIR/php.ini-production" "$PHP_INI_DIR/php.ini"
COPY --link php/conf.d/app.ini $PHP_INI_DIR/conf.d/
COPY --link php/conf.d/app.prod.ini $PHP_INI_DIR/conf.d/app.prod.or.dev.ini
COPY --link php/conf.d/app.prod.ini $PHP_INI_DIR/conf.d/app.ini-production
COPY --link php/conf.d/app.dev.ini $PHP_INI_DIR/conf.d/app.ini-development
COPY --link php/php-fpm.d/zz-docker.conf /usr/local/etc/php-fpm.d/zz-docker.conf

# Composer: install package
ENV COMPOSER_ALLOW_SUPERUSER=1
RUN composer install --prefer-dist --no-dev --no-autoloader --no-scripts --no-progress && \
    composer dump-autoload --classmap-authoritative --no-dev && \
    composer run-script --no-dev post-install-cmd && \
    chmod +x bin/console && sync

####################

FROM node:alpine as builder-2

ENV KBIN_HOME=/var/www/kbin

# Copy project
COPY --from=builder-1 $KBIN_HOME $KBIN_HOME

# Yarn: install package
RUN cd $KBIN_HOME && \
    yarn install && \
    yarn build && \
    rm -rf node_modules

####################

FROM base as runner

COPY --from=builder-caddy --link /usr/bin/caddy /usr/sbin/caddy
COPY --link caddy/Caddyfile /etc/caddy/Caddyfile
COPY --from=builder-1 --link $PHP_INI_DIR $PHP_INI_DIR
RUN chown -R $USER:$GROUP $PHP_INI_DIR
COPY --from=builder-1 --link /usr/local/etc/php-fpm.d /usr/local/etc/php-fpm.d
RUN chown -R $USER:$GROUP /usr/local/etc/php-fpm.d
COPY --from=builder-2 --chown=$USER:$GROUP --link $KBIN_HOME $KBIN_HOME

COPY --chown=$USER:$GROUP --chmod=755 --link docker-entrypoint ./
COPY --chown=$USER:$GROUP --chmod=755 --link docker-healthcheck ./
HEALTHCHECK --interval=10s --timeout=3s --retries=3 CMD ["./docker-healthcheck"]

# Switch user
USER $USER:$GROUP

ENTRYPOINT ["./docker-entrypoint"]
