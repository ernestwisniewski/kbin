# Docker deployment guide

## Requirement

1. Docker engine
2. Docker compose plugin

## Installation instruction

### Clone repository

```bash
$ git clone https://codeberg.org/Kbin/kbin-core.git
$ cd kbin-core/docker-v2
$ cp ../.env.example .env
$ mkdir media
$ chown kbin:www-data media
# In the containers, the default user "kbin" has uid 1000,
# and the default group "www-data" has gid 33.
# If you don't have them on the host or they have different id,
# use the following command instead.
$ sudo chown 1000:33 media
```

### Configure `.env`

1. Make sure `REDIS_PASSWORD` has same value in `.env` and `docker-compose.override.yml`.
2. Make sure `POSTGRES_PASSWORD` has same value in `.env` and `docker-compose.override.yml`.
3. Change the following line

    ```env
    DATABASE_URL="postgresql://${POSTGRES_USER}:${POSTGRES_PASSWORD}@127.0.0.1:5432/${POSTGRES_DB}?serverVersion=${POSTGRES_VERSION}&charset=utf8"
    ```

    to

    ```env
    DATABASE_URL="postgresql://${POSTGRES_USER}:${POSTGRES_PASSWORD}@db:5432/${POSTGRES_DB}?serverVersion=${POSTGRES_VERSION}&charset=utf8"
    ```

### Build image and create containers

```bash
$ docker compose build # build the image
# The image will be built in development mode, by default.
# Append "--build-arg MODE=prod" to build in production mode.

$ docker compose up -d # create and start the containers
```

Then, you shoud be able to access the new instance via `http://localhost:9001`.

### Add auxiliary containers to `docker-compose.yml`

Add any auxiliary container as you want. For example, add a nginx container as reverse proxy to provide HTTPS encryption.
