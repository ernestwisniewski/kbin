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
$ chown www-data:www-data media
# If you don't have this user in your machine, use the following instead.
# sudo chown 33:33 media
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

### Create and start the containers

```bash
$ docker compose build # build the image
$ docker compose up -d # create and start the containers
```

Then, you shoud be able to access the new instance via `http://localhost:9001`.
