# Docker deployment guide (alternative approach)

## Requirement

1. Docker Engine
2. Docker Compose V2

    > If you are using Compose V1, replace `docker compose` with `docker-compose` in those commmands below.

## Installation

### Clone repository

```bash
$ git clone https://codeberg.org/Kbin/kbin-core.git
$ cd kbin-core/docker/v2
$ cp ../../.env.example .env
$ cp docker-compose.prod.yml docker-compose.override.yml
$ mkdir media
$ chown kbin:www-data media
# In the containers, the default user "kbin" has uid 1000,
# and the default group "www-data" has gid 33.
# If you don't have them on the host or they have different id,
# use the following command instead.
# $ sudo chown 1000:33 media
```

### Configure `.env`

1. Place your redis password to the variable `REDIS_PASSWORD` in both `.env` and `docker-compose.override.yml`.
2. Place your postgres password to the variable `POSTGRES_PASSWORD` in both `.env` and `docker-compose.override.yml`.
3. Place your rabbitmq password to the variable `RABBITMQ_PASSWORD` in both `.env` and `docker-compose.override.yml`.
4. Place your mercure password to the variable `MERCURE_JWT_PASSWORD` in both `.env` and `docker-compose.override.yml`.
5. In `.env`, change the following line

    ```env
    DATABASE_URL="postgresql://${POSTGRES_USER}:${POSTGRES_PASSWORD}@127.0.0.1:5432/${POSTGRES_DB}?serverVersion=${POSTGRES_VERSION}&charset=utf8"
    ```

    to

    ```env
    DATABASE_URL="postgresql://${POSTGRES_USER}:${POSTGRES_PASSWORD}@db:5432/${POSTGRES_DB}?serverVersion=${POSTGRES_VERSION}&charset=utf8"
    ```

6. In `.env`, change the following two lines

    ```env
    MERCURE_URL=https://example.com/.well-known/mercure
    MERCURE_PUBLIC_URL=https://example.com/.well-known/mercure
    ```

    to

    ```env
    MERCURE_URL=http://mercure/.well-known/mercure
    MERCURE_PUBLIC_URL=https://${SERVER_NAME}/.well-known/mercure
    ```

### Build image and create containers

```bash
$ docker compose build # build the image
# The image will be built in production mode, by default.
# Append "--build-arg MODE=dev" to build in development mode.

$ docker compose up -d # create and start the containers
# The kbin container may restart by itself for several times,
# until others containers are ready.
```

Then, you shoud be able to access the new instance via `http://localhost:9001`.

### Add auxiliary containers to `docker-compose.yml`

Add any auxiliary container as you want. For example, add a nginx container as reverse proxy to provide HTTPS encryption.
