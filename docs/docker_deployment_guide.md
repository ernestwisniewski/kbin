# Docker Deployment Guide (Alternative)

## System Requirements

- Docker Engine
- Docker Compose V2

  > If you are using Compose V1, replace `docker compose` with `docker-compose` in those commands below.

# Admin Docker Guide

**Docker guide is still WIP. Not all the steps have been fully verified yet.**

For bare-metal, see: [Admin Bare-Metal Guide](./admin_guide.md).

> **Note**
> /kbin is still in the early stages of development.

_Note:_ This guide is using the [v2 docker files](https://codeberg.org/Kbin/kbin-core/src/branch/develop/docker/v2).

## System Requirements

- Docker Engine
- Docker Compose V2

  > If you are using Compose V1, replace `docker compose` with `docker-compose` in those commands below.

### Docker Install

The most convenient way to install docker is using the official [convenience script](https://github.com/docker/docs/blob/main/_includes/install-script.md)
provided at [get.docker.com](https://get.docker.com/):

```bash
curl -fsSL https://get.docker.com -o get-docker.sh
sudo sh get-docker.sh
```

Alternatively, you can follow the [Docker install documentation](https://docs.docker.com/engine/install/) for your platform.

Once Docker is installed on your system, it is recommended to create a `docker` group and add it to your user:

```bash
sudo groupadd docker
sudo usermod -aG docker $USER
```

## Kbin Installation

### Preparation

Clone git repository:

```bash
git clone https://codeberg.org/Kbin/kbin-core.git
cd kbin-core
```

Build the Docker image:

> **Note** 
> If you're using a version of Docker Engine earlier than 23.0, run `export DOCKER_BUILDKIT=1`, prior to building the image.  This does not apply to users running Docker Desktop.  More info can be found [here](https://docs.docker.com/build/buildkit/#getting-started)

```bash
docker build -t kbin -f docker/v2/Dockerfile .
```

Create config files and storage directories:

```bash
cd docker/v2
cp ../../.env.example_v2 .env
cp docker-compose.prod.yml docker-compose.override.yml
mkdir -p storage/media storage/caddy_config storage/caddy_data
sudo chown 1000:82 storage/media storage/caddy_config storage/caddy_data
```

### Configure `.env`

1. Choose your Redis password, PostgreSQL password, RabbitMQ password, and Mercure password.
2. Place them in the corresponding variables in both `.env` and `docker-compose.override.yml`.
3. Change the values in your `.env` file as followings. (If you change the service names and the listening ports of the services in your `docker-compose.yml`, update the following values correspondingly.)

```env
REDIS_HOST=redis:6379
POSTGRES_HOST=db:5432
RABBITMQ_HOST=rabbitmq:5672
MERCURE_HOST=www:80
```

### Configure OAuth2 keys

1. Create an RSA key pair using OpenSSL:
```bash
mkdir ./config/oauth2/
# If you protect the key with a passphrase, make sure to remember it!
# You will need it later
openssl genrsa -des3 -out ./config/oauth2/private.pem 4096
openssl rsa -in ./config/oauth2/private.pem --outform PEM -pubout -out ./config/oauth2/public.pem
```
2. Generate a random hex string for the OAuth2 encryption key
```bash
openssl rand -hex 16
```
3. Add the public and private key paths to `.env`
```env
OAUTH_PRIVATE_KEY=%kernel.project_dir%/config/oauth2/private.pem
OAUTH_PUBLIC_KEY=%kernel.project_dir%/config/oauth2/public.pem
OAUTH_PASSPHRASE=<Your (optional) passphrase from above here>
OAUTH_ENCRYPTION_KEY=<Hex string generated in previous step>
```

### Running the containers

By default, `docker compose` will execute the `docker-compose.yml` and `docker-compose.override.yml` files.

Run the container in the background (`-d` means detached, but this can also be omitted for testing):

```bash
docker compose up -d
```

See your running containers via: `docker ps`.

Then, you should be able to access the new instance via [http://localhost](http://localhost).  
You can also access the RabbitMQ management UI via [http://localhost:15672](http://localhost:15672).

### Kbin first setup

To create a new admin user (without email verification), please change the `username`, `email` and `password` below:

```bash
docker compose exec php bin/console kbin:user:create <username> <email@example.com> <password>
docker compose exec php bin/console kbin:user:admin <username>
```

```bash
docker compose exec php bin/console kbin:ap:keys:update
```

Next, log in and create a magazine named “random” to which unclassified content from the Fediverse will flow.

### Add auxiliary containers to `docker-compose.yml`

Add any auxiliary container as you want. For example, add a Nginx container as a reverse proxy to provide HTTPS encryption.

## Uploaded media files

Uploaded media files (e.g., photos uploaded by users) will be stored on the host directory `storage/media`. They will be served by the Caddy web server in the `www` container as static files.

Make sure `KBIN_STORAGE_URL` in your `.env` configuration file is set to be `https://yourdomain.tld/media` (assuming you set up Nginx with SSL certificate by now).

You can also serve those media files on another server by mirroring the files at `storage/media` and changing `KBIN_STORAGE_URL` correspondingly.

## File system ACL support

The file system ACL is disabled by default, in the `kbin` image. You can set the environment variable `ENABLE_ACL=1` to enable it. Remember that not all file systems support ACL. This will cause an error if you enable file system ACL for such file systems.

## Production

If you created the file `docker-compose.override.yml` with your configs (`cp docker-compose.prod.yml docker-compose.override.yml`), running production would be the same command:

```bash
docker compose up -d
```

See also the official: [Deploying in Production guide](https://github.com/dunglas/symfony-docker/blob/main/docs/production.md).

If you want to deploy your app on a cluster of machines, you can
use [Docker Swarm](https://docs.docker.com/engine/swarm/stack-deploy/), which is compatible with the provided Compose
files.

## Clear cache

```bash
docker compose exec php bin/console cache:clear
docker compose exec redis redis-cli
> auth REDIS_PASSWORD
> FLUSHDB
```

## Backup and restore

```bash
docker exec -it container_id pg_dump -U kbin kbin > dump.sql
docker compose exec -T database psql -U kbin kbin < dump.sql
```

