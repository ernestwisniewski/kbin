# Upgrade

## Bare Metal / VM Upgrade

If you perform a kbin upgrade (eg. `git pull`), you need to be aware to run the following commands after each upgrade:

```bash
composer install --no-dev
APP_ENV=prod APP_DEBUG=0 php bin/console cache:clear
php bin/console doctrine:migrations:migrate
composer clear-cache
yarn
yarn build
```

And when needed also: `sudo redis-cli FLUSHDB` to get rid of Redis cache issues.

When you are running /kbin in development mode for some reason. Execute the following instead after an update:

```bash
composer install
APP_ENV=dev APP_DEBUG=1 php bin/console cache:clear
php bin/console doctrine:migrations:migrate
composer clear-cache
yarn
yarn build
```

## Docker Upgrade

_Note:_ When you're using the [Docker v2 guide](docker/v2/), then the database migration is executed during the Docker container start-up.

```bash
$ docker compose exec php bin/console cache:clear
$ docker compose exec redis redis-cli
> auth REDIS_PASSWORD
> FLUSHDB
```
