# Kbin

[![Maintainability](https://api.codeclimate.com/v1/badges/ee285c05da04524ea2f9/maintainability)](https://codeclimate.com/github/ernestwisniewski/kbin/maintainability)
[![Test Coverage](https://api.codeclimate.com/v1/badges/ee285c05da04524ea2f9/test_coverage)](https://codeclimate.com/github/ernestwisniewski/kbin/test_coverage)

A reddit-like content aggregator and micro-blogging platform for the fediverse.

This is a very early beta version, and a lot of features are currently broken or in active development, such as federation.

|     |     |     |
| --- | --- | --- |
![](assets/screenshots/s1.png)  |  ![](assets/screenshots/s2.png)  |  ![](assets/screenshots/s3.png)

* https://kbin.info - project website
* **https://dev.karab.in - instance for testing purposes only**
* https://karab.in - polish-lang instance

---

### Apps
* [kbin-mobile](https://github.com/ernestwisniewski/kbin-mobile) (Flutter / Dart)

### Libraries
* [kbin-js-client](https://github.com/ernestwisniewski/kbin-js-client) (TypeScript)
* [kbin-dart-client](#) (Dart)

## Getting Started

### Requirements

https://symfony.com/doc/6.1/reference/requirements.html

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

Based on https://github.com/dunglas/symfony-docker

#### Develop
```bash
# Set SMTP, Postgres, Mercure, Elasticsearch, RabbitMQ creds if you need it.
$ cp .env.example .env

$ docker-compose build --pull --no-cache
$ docker-compose up

$ docker-compose exec php bin/console doctrine:fixtures:load
$ docker-compose exec php bin/phpunit
```

#### Production
```bash
$ SERVER_NAME="beta.karab.in" \
APP_SECRET=427f5e2940e5b2472c1b44b2d06e0525 \
CADDY_MERCURE_JWT_SECRET='!ChangeMe!' \
docker-compose -f docker-compose.yml -f docker-compose.prod.yml up -d
```

## Configuration

### Admin user
```bash
# Create new user (without email verification)
$ docker-compose exec php bin/console kbin:user:create username email@exmple.com password
# Grant administrator privileges
$ docker-compose exec php bin/console kbin:user:admin username
```

### Elasticsearch
```bash
$ docker-compose exec php bin/console fos:elastica:create
$ docker-compose exec php bin/console fos:elastica:populate
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
$ docker exec -it [container_id] pg_dump -U kbin kbin > dump_`date +%d-%m-%Y"_"%H_%M_%S`.sql
# Restore
$ docker exec -it [container_id] psql kbin < dump.sql // @todo
```

### Images
```bash
// @todo rsync
```

## Troubleshooting

### Logs
```bash
$ docker-compose logs -f
$ docker-compose exec php tail var/log/prod.log
```

### Cache
```bash
$ docker-compose exec php bin/console cache:clear
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

https://docs.kbin.info - Kbin API Reference

## Contributing

## License

[AGPL-3.0 license](https://github.com/ernestwisniewski/kbin/blob/main/LICENSE)
