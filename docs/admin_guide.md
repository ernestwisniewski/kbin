## Admin guide

Below is a step-by-step description of the process for creating your own instance from the moment a new VPS is created.		
This is a preliminary outline that will help you launch an instance for your own needs. Please note that kbin is still		
in the early stages of development and is currently intended for smaller instances.

If you would like to support the project, you can register using the		
following [affiliate link](https://hetzner.cloud/?ref=8tSPCw0qqIwl).

The VPS is running Debian 11 / Ubuntu 22.04. Redis is used for caching, so it is recommended to have at least 2 CPUs (>2.6 GHz) and 4GB		
of RAM. Filesystem cache can be used too, but it causes significant performance issues under high traffic.

---		

### Install on Bare Metal / VPS

ubuntu 22.04 used for steps here

#### System update / Prerequisites

```bash
$ apt-get update && apt-get upgrade
$ add-apt-repository ppa:ondrej/php
$ apt-get install git redis-server postgresql postgresql-contrib nginx php8.2-common php8.2-fpm php8.2-cli php8.2-amqp php8.2-pgsql php8.2-gd php8.2-curl php8.2-simplexml php8.2-dom php8.2-xml php8.2-redis php8.2-mbstring php8.2-intl unzip
$ curl -sS https://getcomposer.org/installer -o /tmp/composer-setup.php
$ php /tmp/composer-setup.php --install-dir=/usr/local/bin --filename=composer
```

#### Firewall

```bash
// todo 
```

#### Front tools

```bash
$ curl -sL https://deb.nodesource.com/setup_16.x | sudo bash -
$ curl -sL https://dl.yarnpkg.com/debian/pubkey.gpg | gpg --dearmor | sudo tee /usr/share/keyrings/yarnkey.gpg >/dev/null
$ echo "deb [signed-by=/usr/share/keyrings/yarnkey.gpg] https://dl.yarnpkg.com/debian stable main" | sudo tee /etc/apt/sources.list.d/yarn.list
$ apt-get update && apt-get install nodejs yarn

```

#### Create user

```bash
$ adduser kbin
$ usermod -aG sudo kbin
$ usermod -aG www-data kbin
$ su - kbin
```

#### Create path

```bash
$ cd /var/www
$ sudo mkdir kbin
$ sudo chown kbin:www-data kbin
```

#### Clone repo

```bash
$ git clone https://codeberg.org/Kbin/kbin-core.git kbin
$ cd kbin
$ mkdir public/media
$ sudo chmod 777 public/media

$ cp .env.example .env
$ vi .env # esc + !q + enter to exit
or 
$ nano .env
```

Make sure you have substituted all the passwords and configured the basic services in .env file.

#### Service Configuration

PHP:

```bash
$nano sudo nano /etc/php/8.2/fpm/php.ini
```

```
upload_max_filesize = 8M
post_max_size = 8M
memory_limit = 512M
```

Composer:
1. Install files
    * Developer mode:
      ```bash
      composer install
      ```
    * Production mode:
      ```bash
      composer dump-env prod
      composer install --prefer-dist --no-dev --no-autoloader --no-scripts --no-progress
      ```
2. Clear cache (necessary every time the DB is updated, especially relevant in dev environments. Change env to dev when working in developer mode)
    ```bash
    APP_ENV=prod APP_DEBUG=0 php bin/console cache:clear
    composer clear-cache
    ```
3. Set up filesystem permissions for media:
    ```bash
    sudo chown kbin:www-data public/media
    sudo chmod 777 public/media

    # https://symfony.com/doc/current/setup/file_permissions.html
    # if the following commands don't work, try adding `-n` option to `setfacl`
    HTTPDUSER=$(ps axo user,comm | grep -E '[a]pache|[h]ttpd|[_]www|[w]ww-data|[n]ginx' | grep -v root | head -1 | cut -d\  -f1)
    # set permissions for future files and folders
    sudo setfacl -dR -m u:"$HTTPDUSER":rwX -m u:$(whoami):rwX var
    # set permissions on the existing files and folders
    sudo setfacl -R -m u:"$HTTPDUSER":rwX -m u:$(whoami):rwX var
    ```

Redis:

```bash
$ openssl rand 60 | openssl base64 -A
$ OaYOuq6J9HhxMV0sGCeZbaGecphCl4GBfVkCOPkNjkQE1FX9DKpGSCJcDb8UV+AuFKA8tR1PgjGequn1
$ sudo nano /etc/redis/redis.conf

ctrl + w -> # requirepass foobared
Remove the #, change foobared too new password

ctrl + w -> supervised no
Change to systemd, considering Ubuntu

$ sudo systemctl restart redis.service
```

.env: Change the redis host to localhost or proper IP

```conf
REDIS_DNS=redis://${REDIS_PASSWORD}@localhost
```

Postgresql:

```bash
$ sudo -u postgres createuser --createdb --createrole --pwprompt kbin

$ php bin/console doctrine:database:create
$ php bin/console doctrine:migrations:migrate
```

Yarn:

```bash
$ yarn install
$ yarn build
```

Make sure you have substituted all the passwords and configured the basic services.

#### Nginx

```bash
$ sudo nano /etc/nginx/sites-available/kbin.conf
$ sudo ln -s /etc/nginx/sites-available/kbin.conf /etc/nginx/sites-enabled/
```

```kbin.conf
server {
    server_name domain.tld www.domain.tld;
    root /var/www/kbin/public;

    location / {
        # try to serve file directly, fallback to app.php
        try_files $uri /index.php$is_args$args;
    }
    location ~ ^/index\.php(/|$) {
        default_type application/x-httpd-php;
        fastcgi_pass unix:/var/run/php/php8.2-fpm.sock;
        fastcgi_split_path_info ^(.+\.php)(/.*)$;
        include fastcgi_params;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        fastcgi_param DOCUMENT_ROOT $realpath_root;
        
        # Prevents URIs that include the front controller. This will 404:
        # http://domain.tld/index.php/some-path
        # Remove the internal directive to allow URIs like this
        internal;
    }
    # bypass thumbs cache image files
    location ~ ^/media/cache/resolve {
      expires 1M;
      access_log off;
      add_header Cache-Control "public";
      try_files $uri $uri/ /index.php?$query_string;
    }
    location ~* .(js|webp|jpg|jpeg|gif|png|css|tgz|gz|rar|bz2|doc|pdf|ppt|tar|wav|bmp|rtf|swf|ico|flv|txt|woff|woff2|svg)$ {
        expires 30d;
        add_header Pragma "public";
        add_header Cache-Control "public";
    }

   # return 404 for all other php files not matching the front controller
   # this prevents access to other php files you don't want to be accessible.
   location ~ \.php$ {
     return 404;
   }
   
   client_max_body_size 10M;
	
   error_log /var/log/nginx/kbin_error.log;
   access_log /var/log/nginx/kbin_access.log;
}
```

#### Let’s Encrypt SSL/TLS

```bash
$ sudo apt-get install certbot
$ sudo apt-get install python3-certbot-nginx
$ sudo certbot --nginx -d domain.tld -d www.domain.tld
$ crontab -e
```

```
0 12 * * * /usr/bin/certbot renew --quiet
```

Save and close the file.

#### Queues

##### Install RabbitMQ (optional)

// @todo

```bash
nano .env
```

```
MESSENGER_TRANSPORT_DSN=doctrine://default

or

RABBITMQ_PASSWORD=!ChangeThisRabbitPass!
MESSENGER_TRANSPORT_DSN=amqp://kbin:${RABBITMQ_PASSWORD}@localhost:5672/%2f/messages
```

##### Setup supervisor

```bash
sudo apt-get install supervisor
sudo nano /etc/supervisor/conf.d/messenger-worker.conf
```

```
[program:messenger-kbin]
command=php /var/www/kbin/bin/console messenger:consume async --time-limit=3600
user=ubuntu
numprocs=2
startsecs=0
autostart=true
autorestart=true
startretries=10
process_name=%(program_name)s_%(process_num)02d

[program:messenger-ap]
command=php /var/www/kbin/bin/console messenger:consume async_ap --time-limit=3600
user=ubuntu
numprocs=2
startsecs=0
autostart=true
autorestart=true
startretries=10
process_name=%(program_name)s_%(process_num)02d
```

Save and close the file.

```bash
$ sudo supervisorctl reread
$ sudo supervisorctl update
$ sudo supervisorctl start all
```

#### Install mercurejs

// @todo https://mercure.rocks/

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

Test postgresql connections if using a remote server, same with redis. Ensure no firewall blocking is enabled for the
remote ip.

---

### Install with Docker

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

Make sure you have substituted all the passwords and configured the basic services in .env file.

The Dockerfile is based on [symfony-docker](https://github.com/dunglas/symfony-docker).

#### Install Docker

The most convenient way to install docker is using the official [convenience script](https://github.com/docker/docs/blob/main/_includes/install-script.md)
provided at [https://get.docker.com/]:

```bash
$ curl -fsSL https://get.docker.com -o get-docker.sh
$ sudo sh get-docker.sh
```

Alternatively, you can follow the [Docker install documentation](https://docs.docker.com/engine/install/) for your platform.

Once Docker is installed on your system, it is recommended to create a `docker` group and add it to your user:

```bash
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

Open [https://kbin.localhost](https://kbin.localhost) in your favorite web browser and accept the auto-generated TLS
certificate

#### Production

```bash
$ docker compose -f docker-compose.yml -f docker-compose.prod.yml up -d --build
```

[https://github.com/dunglas/symfony-docker/blob/main/docs/production.md](https://github.com/dunglas/symfony-docker/blob/main/docs/production.md)

If you want to deploy your app on a cluster of machines, you can
use [Docker Swarm](https://docs.docker.com/engine/swarm/stack-deploy/), which is compatible with the provided Compose
files.

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

---

### Install without Docker

References:

- [https://symfony.com/doc/current/setup.html](https://symfony.com/doc/current/setup.html)
- [https://symfony.com/doc/current/deployment.html](https://symfony.com/doc/current/deployment.html)
- [https://symfony.com/doc/current/setup/web_server_configuration.html](https://symfony.com/doc/current/setup/web_server_configuration.html)
- [https://symfony.com/doc/current/messenger.html#deploying-to-production](https://symfony.com/doc/current/messenger.html#deploying-to-production)
- [https://codingstories.net/how-to/how-to-install-and-use-mercure/](https://codingstories.net/how-to/how-to-install-and-use-mercure/)

---

### S3 Images storage (optional)

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
---

### Performence

[https://symfony.com/bundles/LiipImagineBundle/current/optimizations/resolve-cache-images-in-background.html#symfony-messenger](https://symfony.com/bundles/LiipImagineBundle/current/optimizations/resolve-cache-images-in-background.html#symfony-messenger)