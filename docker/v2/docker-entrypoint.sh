#!/bin/sh

# If no additional argument, start server
if [ -z "$@" ]; then
    # Database migration
    php bin/console doctrine:migrations:migrate --no-interaction
    [ $? -ne 0 ] && exit

    # Start php-fpm (at the background)
    php-fpm -D

    # Start nginx
    nginx -g "daemon off;"

# Else execute the argument
else
    exec "$@"
fi
