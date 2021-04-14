# Kbin

[![Maintainability](https://api.codeclimate.com/v1/badges/ee285c05da04524ea2f9/maintainability)](https://codeclimate.com/github/ernestwisniewski/kbin/maintainability)
[![Test Coverage](https://api.codeclimate.com/v1/badges/ee285c05da04524ea2f9/test_coverage)](https://codeclimate.com/github/ernestwisniewski/kbin/test_coverage)

A reddit-like content aggregator and micro-blogging platform for the fediverse - https://kbin.info.

https://dev.karab.in instance for testing purposes only

This is a very early beta version, and a lot of features are currently broken or in active development, such as federation.

## Installation

### Requirements

https://symfony.com/doc/4.2/reference/requirements.html

* PHP version: 8.0 or higher
* GD or Imagemagick php extension
* NGINX / Apache / Caddy
* PostgreSQL
* Mongodb (should be optional soon)
* Mercure (optional)
* RabbitMQ (optional)
* Elasticsearch (optional)

### Install with Docker

Based on https://github.com/dunglas/symfony-docker

#### Develop

```console
# Set SMTP creds if you need it.
$ cp .env.example .env
$ docker-compose up
$ docker-compose exec php bin/console doctrine:fixtures:load
$ docker-compose exec php bin/phpunit
```

#### Production

```console
$ SERVER_NAME=dev.karab.in \
APP_SECRET=secret \
MERCURE_PUBLISHER_JWT_KEY=ChangeMe \
MERCURE_SUBSCRIBER_JWT_KEY=ChangeMe \
docker-compose -f docker-compose.yml -f docker-compose.prod.yml up -d
```

## Configuration

### Admin user

```console
# Create new user (without email verification)
$ docker-compose exec php bin/console kbin:user username email@exmple.com password
# Grant administrator privileges
$ docker-compose exec php bin/console kbin:admin username
```

Next, setup your instance https://localhost/admin

## Federation

https://dunglas.fr/2021/01/schema-generator-3-a-step-towards-redecentralizing-the-web/

https://github.com/api-platform/activity-pub

## Documentation

#### Code

#### API

## Contributing

## License
