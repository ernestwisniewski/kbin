# Upgrade

```8eee91b5f80533f12492c67e9de8ed9289ed1ce0```

```bash
$ docker compose exec php bin/console cache:clear
$ docker compose exec redis redis-cli
> auth REDIS_PASSWORD
> FLUSHDB
```
