FROM php:8.2-fpm-bullseye as base

# Install php extensions, by docker-php-extension-installer
RUN curl -sSL https://github.com/mlocati/docker-php-extension-installer/releases/latest/download/install-php-extensions -o - | \
    sh -s amqp pgsql gd curl simplexml dom xml redis intl pdo_pgsql
## Remark: pdo_pgsql is added.

# Install system prerequisites
RUN apt-get update && apt-get install -y git unzip nginx

# **Environment variables**
ARG MODE=prod \
    UID=1000 \
    GID=33
ENV KBIN_HOME=/var/www/kbin \
    USER=kbin \
    GROUP=www-data

# Create user
RUN adduser --uid $UID -gid $GID -disabled-password --gecos "" $USER

# Create path
RUN mkdir -p $KBIN_HOME && \
    chown $USER:$GROUP $KBIN_HOME
WORKDIR $KBIN_HOME

# PHP configuration (need to this before setting up composer)
RUN if [ "$MODE" = "dev" ]; then \
        PHP_MODE="development"; \
    elif [ "$MODE" = "prod" ]; then \
        PHP_MODE="production"; \
    else \
        echo "Unknown mode: $MODE" && \
        return 1; \
    fi && \
    sed -e "s/upload_max_filesize = [0-9]*M/upload_max_filesize = 8M/p" \
    -e "s/post_max_size = [0-9]*M/post_max_size = 8M/p" \
    -e "s/memory_limit = [0-9]*M/memory_limit = 512M/p" \
    "$PHP_INI_DIR/php.ini-$PHP_MODE" > "$PHP_INI_DIR/php.ini"
RUN echo "chdir = $KBIN_HOME" >> /usr/local/etc/php-fpm.d/www.conf

####################

FROM base as builder

# Install composer
RUN curl -sS https://getcomposer.org/installer -o /tmp/composer-setup.php
RUN php /tmp/composer-setup.php --install-dir=/usr/local/bin --filename=composer

# Install nodejs and yarn
RUN curl -sL https://deb.nodesource.com/setup_16.x | bash -
RUN curl -sL https://dl.yarnpkg.com/debian/pubkey.gpg | gpg --dearmor | tee /usr/share/keyrings/yarnkey.gpg >/dev/null
RUN echo "deb [signed-by=/usr/share/keyrings/yarnkey.gpg] https://dl.yarnpkg.com/debian stable main" | tee /etc/apt/sources.list.d/yarn.list
RUN apt-get update && apt-get install -y nodejs yarn

# Swithc user
USER $USER

# Clone repo
RUN git clone https://codeberg.org/Kbin/kbin-core.git $KBIN_HOME && \
    mkdir public/media && \
    chmod 777 public/media && \
    cp .env.example .env

# Composer: Install packages
RUN if [ "$MODE" = "dev" ]; then \
        echo "Composer: development mode" && \
        composer install && \
        composer clear-cache; \
    elif [ "$MODE" = "prod" ]; then \
        echo "Composer: production mode" && \
        composer require symfony/flex && \
        composer dump-env prod && \
        composer install --no-dev --optimize-autoloader && \
        APP_ENV=prod APP_DEBUG=0 php bin/console cache:clear && \
        composer clear-cache; \
    else \
        echo "Unknown mode: $MODE" && \
        return 1; \
    fi

# Yarn: Install, Build, Clean
RUN yarn install && \
    yarn build && \
    yarn cache clean && \
    rm -rf node_modules

####################

FROM base as runner

# Nginx configuration
COPY kbin.conf /etc/nginx/sites-available/kbin.conf
RUN ln -s /etc/nginx/sites-available/kbin.conf /etc/nginx/sites-enabled/
RUN mkdir -p /var/cache/nginx && chown -R $USER:$GROUP /var/cache/nginx && \
    mkdir -p /var/lib/nginx && chown -R $USER:$GROUP /var/lib/nginx && \
    mkdir -p /var/log/nginx && chown -R $USER:$GROUP /var/log/nginx
RUN touch /var/run/nginx.pid && \
    chown -R $USER:$GROUP /var/run/nginx.pid

# Switch user
USER $USER

COPY --from=builder --chown=$USER:$GROUP $KBIN_HOME $KBIN_HOME
COPY --chown=$USER:$GROUP --chmod=755 docker-entrypoint.sh ./
ENTRYPOINT ["./docker-entrypoint.sh"]
