## Admin guide

Below is a step-by-step description of the process for creating your own instance from the moment a new VPS is created.  This is a preliminary outline that will help you launch an instance for your own needs. Please note that kbin is still in the early stages of development and is currently intended for smaller instances.

If you would like to support the project, you can register using the following [affiliate link](https://hetzner.cloud/?ref=8tSPCw0qqIwl).

The VPS is running Debian 11. Redis is used for caching, so it is recommended to have at least 2 CPUs (>2.6 GHz) and 4GB of RAM. Filesystem cache can be used too, but it causes significant performance issues under high traffic.

#### System update

```bash
$ apt-get update && apt-get upgrade
```

#### Firewall

```bash
// todo 
```

#### Create user

```bash
$ adduser kbin
$ usermod -aG sudo kbin
$ su kbin
$ cd ~
```

#### Front tools

```bash
$ curl -sL https://deb.nodesource.com/setup_16.x | sudo bash -
$ curl -sL https://dl.yarnpkg.com/debian/pubkey.gpg | gpg --dearmor | sudo tee /usr/share/keyrings/yarnkey.gpg >/dev/null
$ sudo apt-get install -y nodejs
$ echo "deb [signed-by=/usr/share/keyrings/yarnkey.gpg] https://dl.yarnpkg.com/debian stable main" | sudo tee /etc/apt/sources.list.d/yarn.list
$ sudo apt-get update && sudo apt-get install yarn

```


#### Clone repo

```bash
$ sudo apt-get install git
$ git clone https://codeberg.org/Kbin/kbin-core.git kbin
$ cd kbin
$ mkdir public/media
$ sudo chown 82:82 public/media
$ sudo chown 82:82 var
$ cp .env.example .env
$ vi .env # esc + !q + enter to exit
or 
$ nano .env
```


Make sure you have substituted all the passwords and configured the basic services.

### Install with Docker

The Dockerfile is based on [symfony-docker](https://github.com/dunglas/symfony-docker).

[https://docs.docker.com/engine/install/debian/](https://docs.docker.com/engine/install/debian/)

```bash
$ sudo apt-get install ca-certificates curl gnupg
$ sudo install -m 0755 -d /etc/apt/keyrings
$ curl -fsSL https://download.docker.com/linux/debian/gpg | sudo gpg --dearmor -o /etc/apt/keyrings/docker.gpg
$ sudo chmod a+r /etc/apt/keyrings/docker.gpg
$ echo \
  "deb [arch="$(dpkg --print-architecture)" signed-by=/etc/apt/keyrings/docker.gpg] https://download.docker.com/linux/debian \
  "$(. /etc/os-release && echo "$VERSION_CODENAME")" stable" | \
  sudo tee /etc/apt/sources.list.d/docker.list > /dev/null
$ sudo apt-get update
$ sudo apt-get install docker-ce docker-ce-cli containerd.io docker-buildx-plugin docker-compose-plugin
$ sudo apt-get install docker-compose-plugin
$ sudo groupadd docker
$ sudo usermod -aG docker $USER
```

#### Run containers

```bash
$ docker compose build --pull --no-cache # build fresh images
$ docker compose up # the logs will be displayed in the current shell
$ docker compose down --remove-orphans # stop the Docker containers.
```

#### Build front

The first startup will fail, so while the container is starting, execute the following commands:

```bash
$ yarn install
$ yarn build
$ docker compose down && docker compose up
```

Open [https://app.localhost](https://app.localhost) in your favorite web browser and accept the auto-generated TLS certificate

#### Production

```bash
$ docker compose -f docker-compose.yml -f docker-compose.prod.yml up -d --build
```

[https://github.com/dunglas/symfony-docker/blob/main/docs/production.md](https://github.com/dunglas/symfony-docker/blob/main/docs/production.md)

If you want to deploy your app on a cluster of machines, you can use [Docker Swarm](https://docs.docker.com/engine/swarm/stack-deploy/), which is compatible with the provided Compose files.

#### Configuration

```bash
# Create new user (without email verification)
$ docker compose exec php bin/console kbin:user:create username email@exmple.com password
# Grant administrator privileges
$ docker compose exec php bin/console kbin:user:admin username
```

```bash
docker compose exec php bin/console kbin:ap:keys:update
```
Next, log in and create a magazine named "random" to which unclassified content from the fediverse will flow.

#### Admin panel

[https://instance-domain.com/admin/settings](https://instance-domain.com/admin/settings)

### Install without Docker

References:

- [https://symfony.com/doc/current/setup.html](https://symfony.com/doc/current/setup.html)
- [https://symfony.com/doc/current/deployment.html](https://symfony.com/doc/current/deployment.html)
- [https://symfony.com/doc/current/setup/web_server_configuration.html](https://symfony.com/doc/current/setup/web_server_configuration.html)
- [https://symfony.com/doc/current/messenger.html#deploying-to-production](https://symfony.com/doc/current/messenger.html#deploying-to-production)
- [https://codingstories.net/how-to/how-to-install-and-use-mercure/](https://codingstories.net/how-to/how-to-install-and-use-mercure/)