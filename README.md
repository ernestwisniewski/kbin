# Kbin

[![Maintainability](https://api.codeclimate.com/v1/badges/ee285c05da04524ea2f9/maintainability)](https://codeclimate.com/github/ernestwisniewski/kbin/maintainability)
[![Test Coverage](https://api.codeclimate.com/v1/badges/ee285c05da04524ea2f9/test_coverage)](https://codeclimate.com/github/ernestwisniewski/kbin/test_coverage)

A reddit-like content aggregator and micro-blogging platform for the fediverse.

This is a very early beta version, and a lot of features are currently broken or in active development, such as federation.

|     |     |     |
| --- | --- | --- |
![](assets/screenshots/s1.png)  |  ![](assets/screenshots/s2.png)  |  ![](assets/screenshots/s3.png)

* [https://kbin.pub](https://kbin.pub) - project website
* [https://dev.karab.in](https://dev.karab.in) - instance for testing purposes only
* [https://karab.in](https://karab.in) - polish-lang instance

---

### Apps

* [kbin-mobile](https://github.com/ernestwisniewski/kbin-mobile) (Flutter / Dart)

### Libraries

* [kbin-js-client](https://github.com/ernestwisniewski/kbin-js-client) (TypeScript)
* [kbin-dart-client](#) (Dart)

## Getting Started

### Requirements

[https://symfony.com/doc/6.1/reference/requirements.html](https://symfony.com/doc/6.1/reference/requirements.html)

* PHP version: 8.1 or higher
* GD or Imagemagick php extension
* NGINX / Apache / Caddy
* PostgreSQL
* Redis (optional)
* Mercure (optional)
* RabbitMQ (optional)
* Elasticsearch (optional)
* Cardano Node, Cardano Wallet (optional)

### Frontend

https://github.com/symfony/ux

```bash
$ yarn install
$ yarn build
```

### Install with Docker

Based on [https://github.com/dunglas/symfony-docker](https://github.com/dunglas/symfony-docker)

#### Develop

1. If not already done, [install Docker Compose](https://docs.docker.com/compose/install/)
2. Run `docker compose build --pull --no-cache` to build fresh images
3. Run `docker compose up` (the logs will be displayed in the current shell)
4. Open `https://app.localhost` in your favorite web browser and [accept the auto-generated TLS certificate](https://stackoverflow.com/a/15076602/1352334)
5. Run `docker compose down --remove-orphans` to stop the Docker containers.

```bash
$ docker compose exec php bin/console doctrine:fixtures:load
$ docker compose exec php bin/phpunit

# Using Xdebug
# Linux / Mac
$ XDEBUG_MODE=debug docker compose up -d
# Windows
$ set XDEBUG_MODE=debug&& docker compose up -d&set XDEBUG_MODE=
```

#### Production

```bash
$ APP_ENV=dev SERVER_NAME=dev.karab.in \
APP_SECRET=acme \
CADDY_MERCURE_JWT_SECRET='!ChangeThisMercureHubJWTSecretKey!' \
POSTGRES_USER=kbin \
POSTGRES_PASSWORD=acme \
POSTGRES_DB=kbin \
CADDY_MERCURE_URL="https://example.com/.well-known/mercure" \
KBIN_DEFAULT_LANG=pl \
docker compose -f docker-compose.yml -f docker-compose.prod.yml up -d
```

#### Deploying on Multiple Nodes

If you want to deploy your app on a cluster of machines, you can use [Docker Swarm](https://docs.docker.com/engine/swarm/stack-deploy/), which is
compatible with the provided Compose files.

## Configuration

### Admin user

```bash
# Create new user (without email verification)
$ docker compose exec php bin/console kbin:user:create username email@exmple.com password
# Grant administrator privileges
$ docker compose exec php bin/console kbin:user:admin username
```

### Media

```bash
$ mkdir public/media
$ chmod 755 public/media
$ chown 82:82 public/media
```

### Elasticsearch

```bash
$ docker compose exec php bin/console fos:elastica:create
$ docker compose exec php bin/console fos:elastica:populate
```

### JWT keys

```bash
// @todo 
```

Next, set up your instance https://localhost/admin

## Backup and restore

### Database

```bash
# Backup
$ docker exec -it database pg_dump -U symfony app > dump_`date +%d-%m-%Y"_"%H_%M_%S`.sql
# Restore
$ docker compose exec -T database psql -U symfony app < dump.sql
```

### Images

```bash
// @todo rsync
```

## Troubleshooting

### Editing Permissions on Linux

If you work on linux and cannot edit some of the project files right after the first installation, you can run `docker compose run --rm php chown -R $(id -u):$(id -g) .` to set yourself as owner of the project files that were created by the docker container.

### Logs

```bash
$ docker compose logs -f
$ docker compose exec php tail var/log/prod.log
```

### Cache

```bash
$ docker compose exec php bin/console cache:clear
```

## Federation

### Official Documents

* [ActivityPub standard](https://www.w3.org/TR/activitypub/)
* [ActivityPub vocabulary](https://www.w3.org/TR/activitystreams-vocabulary/)

### Unofficial Sources

* [A highly opinionated guide to learning about ActivityPub](https://tinysubversions.com/notes/reading-activitypub/)
* [ActivityPub as it has been understood](https://flak.tedunangst.com/post/ActivityPub-as-it-has-been-understood)
* [Schema Generator 3: A Step Towards Redecentralizing the Web!](https://dunglas.fr/2021/01/schema-generator-3-a-step-towards-redecentralizing-the-web/)
* [API Platform ActivityPub](https://github.com/api-platform/activity-pub)

## Documentation

* [Kbin REST API Reference](https://docs.kbin.pub)
* [Kbin ActivityPub Reference](https://docs.kbin.pub#activity-pub)
* Kbin GraphQL Reference


## Sponsors and partners

[<img src="public/partners/blackfire-io.png" alt="blackfire.io" style="width:350px;"/>](https://www.blackfire.io)


## Contributing


## License

[AGPL-3.0 license](https://github.com/ernestwisniewski/kbin/blob/main/LICENSE)
