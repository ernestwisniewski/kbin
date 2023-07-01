# Docker deployment guide (alternative approach)

## Requirement

1. Docker Engine
2. Docker Compose V2

    > If you are using Compose V1, replace `docker compose` with `docker-compose` in those commmands below.

## Installation

### Preparation

```bash
# Clone repository
$ git clone https://codeberg.org/Kbin/kbin-core.git
$ cd kbin-core

# Build image
$ docker build -t kbin -f docker/v2/Dockerfile .

# Create config files and storage directories
$ cd docker/v2
$ cp ../../.env.example .env
$ cp docker-compose.prod.yml docker-compose.override.yml
$ mkdir -p storage/media storage/caddy_condig storage/caddy_data
$ sudo chown 1000:82 storage/media storage/caddy_condig storage/caddy_data
```

### Configure `.env`

1. Choose your Redis password, PostgreSQL password, RabbitMQ password, and Mercure password.
2. Place them in the corresponding variables in both `.env` and `docker-compose.override.yml`.
3. In `.env`, change the following line

    ```env
    DATABASE_URL="postgresql://${POSTGRES_USER}:${POSTGRES_PASSWORD}@127.0.0.1:5432/${POSTGRES_DB}?serverVersion=${POSTGRES_VERSION}&charset=utf8"
    ```

    to

    ```env
    DATABASE_URL="postgresql://${POSTGRES_USER}:${POSTGRES_PASSWORD}@db:5432/${POSTGRES_DB}?serverVersion=${POSTGRES_VERSION}&charset=utf8"
    ```

4. In `.env`, change the following two lines

    ```env
    MERCURE_URL=https://example.com/.well-known/mercure
    MERCURE_PUBLIC_URL=https://example.com/.well-known/mercure
    ```

    to

    ```env
    MERCURE_URL=http://www:80/.well-known/mercure
    MERCURE_PUBLIC_URL=https://${SERVER_NAME}/.well-known/mercure
    ```

### Create and start containers

```bash
$ docker compose up -d # create and start the containers
```

Then, you shoud be able to access the new instance via `http://localhost:80`. You can also access RabbitMQ management UI via `http://localhost:15672`.

### Add auxiliary containers to `docker-compose.yml`

Add any auxiliary container as you want. For example, add a nginx container as reverse proxy to provide HTTPS encryption.

## Uploaded media files

Uploaded media files (e.g. photos uploaded by users) will be stored at the host directory `storage/media`. They will be served by the Caddy web server in the `www` container as static files, so make sure `KBIN_STORAGE_URL` in your `.env` configuration file is set to be `https://instance-domain.com` (assuming your instance domain name is `instance-domain.com` and the url `https://instance-domain.com` can reach your instance). You also can serve those media files on another server by mirroring the files at `stroage/media` and changing `KBIN_STORAGE_URL` correspondingly.

## Filesystem ACL support

The filesystem ACL is disabled by default, in the `kbin` image. You can set the environment variable `ENABLE_ACL=1` to enable it. Remember that not all filesystems support ACL. This will cause an error if you enable filesystem ACL for such filesystems.
