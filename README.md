# Kbin

A reddit-like content aggregator and micro-blogging platform for the fediverse - https://kbin.info.

https://dev.karab.in instance for testing purposes only

This is a very early beta version, and a lot of features are currently broken or in active development, such as federation.

## Installation
### Requirements

* PHP version: 8.0 or higher
* GD or Imagemagick php extension
* Mongodb (should be optional soon)

https://symfony.com/doc/4.2/reference/requirements.html

### Setting up the local environment (Debian 10)
#### NGINX
#### PostgreSQL
#### Mercure (optional)
#### RabbitMQ (optional)
#### Elasticsearch (optional)
### Install with Docker

`cp .env.example .env`

`docker-compose up`

`docker-compose exec php bin/console doctrine:migrations:migrate`

`docker-compose exec php bin/console doctrine:fixtures:load`

`docker-compose exec php bin/phpunit`

## Configuration
### Migrations
### Fixtures
### Tests

## Federation

## Documentation
### REST API

## Contributing

## License
