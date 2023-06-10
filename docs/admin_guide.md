# Kbin

Kbin is a decentralized content aggregator and microblogging platform running on the Fediverse network. It can communicate with many other ActivityPub services, including Mastodon, Lemmy, Pleroma, Peertube.

The initiative aims to promote a free and open internet.

## Introduction

The platform is divided into thematic categories called magazines. By default, any user can create their own magazine and automatically become its owner. Then they receive a number of administrative tools that will help them personalize and moderate the magazine, including appointing moderators from among other users.

Content from the Fediverse is also cataloged based on groups or tags. A registered user can follow magazines, other users or domains and create his own personalized homepage. There is also the option to block unwanted topics.

Content can be posted on the main page - external links and more relevant articles or on microblog section - aggregating short posts. All content can be additionally categorized and labeled. Great possibilities to search for interesting topics and people easily is something that distinguishes Kbin.

Platform is equally suitable for a small personal instance for friends and family, a school or university community, company platform or a general instance with thousands of active users.

## User guide

### Customization

Everyone has the ability to customize the appearance to suit your preferences. In the sidebar, you'll find an options button that allows you to adjust a variety of settings, including the ability to choose from four different templates, enable automatic refreshing of posts and comments, activate infinite scroll, and enable automatic media previews.

By using these options, you can completely transform the appearance of the platform to fit your personal style. Whether you prefer a minimalist design or a more colorful and lively look, you can easily make the changes that will make your experience on platform more enjoyable.

So don't be afraid to experiment with the various options available in the sidebar. You might be surprised at just how much you can change the appearance of the platform to suit your preferences.

(pic1)

### Register account

The process of registering for a user account on a platform usually involves providing a username (which will also serve as your identifier in the fediverse), password, and email address to receive an activation link.

Another option is to create an account through social media platforms such as Google, Facebook, or Github. In this case, you can use your social media login credentials to sign up, but you will need to visit your user panel and set up your username before you can take any actions on the platform. However, **you will have only up to an hour after registration** to set up your default username before this option expires (Settings > Profile).

(pic2)

### User settings

You are now ready to start using /kbin to connect with others. After registering, you will be directed to your account settings where you can personalize even more settings to make your experience on our platform even better.

You can access your account settings at any time by clicking on your username located in the header.

(pic3)

We've included a wide range of options that will allow you to customize your experience. Take your time to check all the options.


* **General:** In this section, you can set your preferred home page (all, subscribed, moderated, favorites), hide adult content, set user tagging options, adjust privacy settings, and configure notification settings.

* **Profile:** Here, you can write a few words about yourself (which will be visible in the "People" section), add an avatar and cover image.

* **Email:** In this section, you can change your email address. After changing to a new email, you will receive an activation link.

* **Password:** In this section, you can change your account password.

* **Blocks:** Here, you can manage blocked accounts, magazines, and domains.

* **Subscriptions:** In this section, you can manage subscriptions to other user accounts, magazines, and domains.

* **Reports:** In this section, you can manage reports from moderated magazines.

* **Statistics:** Here, you can find some charts and numbers related to your account.

### Feed Timelines

### Fediverse


## Admin guide

Below is a step-by-step description of the process for creating your own instance from the moment a new VPS is created.  This is a preliminary outline that will help you launch an instance for your own needs. Please note that kbin is still in the early stages of development and is currently intended for smaller instances.

If you would like to support the project, you can register using the following [affiliate link](https://hetzner.cloud/?ref=8tSPCw0qqIwl).

The VPS is running Debian 11. Redis is used for caching, so it is recommended to have at least 2 CPUs (>2.6 GHz) and 4GB of RAM. Filesystem cache can be used too, but it causes significant performance issues under high traffic.

### Install on Bare Metal / VPS

#### System update

```bash
$ apt-get update && apt-get upgrade
```

#### Prerequisites
```bash
$ sudo apt install redis-server postgresql postgresql-contrib php-cli unzip
$ curl -sS https://getcomposer.org/installer -o /tmp/composer-setup.php
$ sudo php /tmp/composer-setup.php --install-dir=/usr/local/bin --filename=composer
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
$ sudo apt-get install php8.x-amqp php8.x-pgsql
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
(un-needed chowns?)
- $ sudo chown 82:82 public/media
- $ sudo chown 82:82 var
$ cp .env.example .env
$ vi .env # esc + !q + enter to exit
or 
$ nano .env
```

Make sure you have substituted all the passwords and configured the basic services.

#### Composer Install
```bash
$ composer install
$ composer clear-cache
$ yarn install
$ yarn build
```

#### Run Database creation
```bash
$ php bin/console doctrine:database:create
$ php bin/console doctrine:migrations:migrate
```

#### Nginx considerations
Ensure you restart as you make changes to the config files

```conf
root /home/user/kbin/public;

location / {
    # try to serve file directly, fallback to index.php
    try_files $uri /index.php$is_args$args;
}

location ~ ^/index\.php(/|$) {
  	default_type application/x-httpd-php;
	fastcgi_pass unix:/var/php-fpm/php-fpm.sock;
    fastcgi_split_path_info ^(.+\.php)(/.*)$;
    include fastcgi_params;

    fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
    fastcgi_param DOCUMENT_ROOT $realpath_root;

    # Prevents URIs that include the front controller. This will 404:
    # http://domain.tld/index.php/some-path
    # Remove the internal directive to allow URIs like this
    internal;
}

# return 404 for all other php files not matching the front controller
# this prevents access to other php files you don't want to be accessible.
location ~ \.php$ {
    return 404;
}

```

#### Configuration

```bash
# Create new user (without email verification)
$ php bin/console kbin:user:create username email@exmple.com password
# Grant administrator privileges
$ php bin/console kbin:user:admin username
```

```bash
$ php bin/console kbin:ap:keys:update
```

#### Debugging
Test postgresql connections if using a remote server, same with redis. Ensure no firewall blocking is enabled for the remote ip. Assets showing a 403 most times is a invalid nginx config from my experience.

The original command for the composer install left me with loading issues 500 error, using just the base command however loads with no problems.

```bash
composer install --prefer-dist --no-dev --no-autoloader --no-scripts --no-progress
vs.
composer install
```
---

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

Open [https://kbin.localhost](https://kbin.localhost) in your favorite web browser and accept the auto-generated TLS certificate

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
$ docker compose exec php bin/console kbin:ap:keys:update
```

Next, log in and create a repository named "random" to which unclassified content from the fediverse will flow.

#### Admin panel

[https://instance-domain.com/admin/settings](https://instance-domain.com/admin/settings)

#### Clear cache

```bash
$ docker compose exec php bin/console cache:clear
$ docker compose exec redis redis-cli
> auth REDIS_PASSWORD
> FLUSHDB
```

#### Backup and restore

```bash
$ docker exec -it container_id pg_dump -U kbin kbin > dump.sql
$ docker compose exec -T database psql -U kbin kbin < dump.sql
```

#### S3 Images storage (optional)

.env

``` 
# S3 storage (optional)
S3_KEY=
S3_SECRET=
S3_BUCKET=media.karab.in
S3_REGION=eu-central-1
S3_VERSION=latest
```

config/packages/oneup_flysystem.yaml

```yaml
oneup_flysystem:
    adapters:
        default_adapter:
            local:
                location: "%kernel.project_dir%/public/media"

        kbin.s3_adapter:
            awss3v3:
                client: kbin.s3_client
                bucket: '%amazon.s3.bucket%'

    filesystems:
        public_uploads_filesystem:
            adapter: kbin.s3_adapter
            alias: League\Flysystem\Filesystem
```

```yaml
// todo thumbnails
```

### Install without Docker

References:

- [https://symfony.com/doc/current/setup.html](https://symfony.com/doc/current/setup.html)
- [https://symfony.com/doc/current/deployment.html](https://symfony.com/doc/current/deployment.html)
- [https://symfony.com/doc/current/setup/web_server_configuration.html](https://symfony.com/doc/current/setup/web_server_configuration.html)
- [https://symfony.com/doc/current/messenger.html#deploying-to-production](https://symfony.com/doc/current/messenger.html#deploying-to-production)
- [https://codingstories.net/how-to/how-to-install-and-use-mercure/](https://codingstories.net/how-to/how-to-install-and-use-mercure/)
