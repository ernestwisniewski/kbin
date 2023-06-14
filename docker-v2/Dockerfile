FROM php:8.2-fpm-bullseye as base

# Install php extensions, by docker-php-extension-installer
RUN curl -sSL https://github.com/mlocati/docker-php-extension-installer/releases/latest/download/install-php-extensions -o - | \
    sh -s amqp pgsql gd curl simplexml dom xml redis intl pdo_pgsql
## Remark: pdo_pgsql is added.

# Install system prerequisites
RUN apt-get update && apt-get install -y git unzip nginx

# **Environment variables**
ARG MODE=dev
ENV KBIN_HOME=/var/www/kbin \
    DEFAULT_USER=kbin

# Create user
RUN addgroup --gid 1000 kbin && \
    adduser --uid 1000 -gid 1000 --disabled-password --gecos "" $DEFAULT_USER && \
    usermod -aG www-data $DEFAULT_USER

# Create path
RUN mkdir -p $KBIN_HOME && \
    chown $DEFAULT_USER:$DEFAULT_USER $KBIN_HOME
WORKDIR $KBIN_HOME

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
USER $DEFAULT_USER

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
        composer dump-env prod && \
        composer install --prefer-dist --no-dev --no-autoloader --no-scripts --no-progress && \
        APP_ENV=prod APP_DEBUG=0 php bin/console cache:clear && \
        composer clear-cache; \
    else \
        echo "Unknown mode: $MODE" && \
        return 1; \
    fi
## TODO: The commands of prod mode causes error during building the docker image.
##       Still under investigating.

# Yarn: Install, Build, Clean
RUN yarn install && \
    yarn build && \
    yarn cache clean && \
    rm -rf node_modules

RUN rm .env

####################

FROM base as runner

# PHP configuration
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

# Nginx configuration
COPY kbin.conf /etc/nginx/sites-available/kbin.conf
RUN ln -s /etc/nginx/sites-available/kbin.conf /etc/nginx/sites-enabled/
RUN mkdir -p /var/cache/nginx && chown -R $DEFAULT_USER:$DEFAULT_USER /var/cache/nginx && \
    mkdir -p /var/lib/nginx && chown -R $DEFAULT_USER:$DEFAULT_USER /var/lib/nginx && \
    mkdir -p /var/log/nginx && chown -R $DEFAULT_USER:$DEFAULT_USER /var/log/nginx
RUN touch /var/run/nginx.pid && \
    chown -R $DEFAULT_USER:$DEFAULT_USER /var/run/nginx.pid

# Set file permissions for symfony
# https://symfony.com/doc/current/setup/file_permissions.html
# if the following commands don't work, try adding `-n` option to `setfacl`
#RUN HTTPDUSER=$(ps axo user,comm | grep -E '[a]pache|[h]ttpd|[_]www|[w]ww-data|[n]ginx' | grep -v root | head -1 | cut -d\  -f1)
# set permissions for future files and folders
#RUN setfacl -dR -m u:"$HTTPDUSER":rwX -m u:$(whoami):rwX var
# set permissions on the existing files and folders
#RUN setfacl -R -m u:"$HTTPDUSER":rwX -m u:$(whoami):rwX var
##
## Remark: The php docker image doesn't have `setfacl` by default.
##         Not sure if it is safe to remove these commands.

# Switch user
USER $DEFAULT_USER

COPY --from=builder --chown=$DEFAULT_USER:$DEFAULT_USER $KBIN_HOME $KBIN_HOME
COPY --chown=$DEFAULT_USER:$DEFAULT_USER --chmod=755 entry-point.sh ./
ENTRYPOINT ["./entry-point.sh"]
