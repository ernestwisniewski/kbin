# Admin Bare Metal/VM Guide

Below is a step-by-step guide of the process for creating your own /kbin instance from the moment a new VPS/VM is created or directly on bare-metal.  
This is a preliminary outline that will help you launch an instance for your needs.

For Docker, see: [Admin Deployment Guide](./docker_deployment_guide.md).

> **Note**
> /kbin is still in the early stages of development.

If you would like to support the project, you can register using the following [affiliate link](https://hetzner.cloud/?ref=8tSPCw0qqIwl).

This guide is aimed for Debian / Ubuntu distribution servers, but it could run on any modern Linux distro. This guide will, however, will use the `apt` commands.

## Minimum hardware requirements

**CPU:** 2 cores (>2.5 GHz)  
**RAM:** 4 GB (more is recommended for large instances)  
**Storage:** 20 GB (more is recommended, especially if you have a lot of remote/local magazines or have a lot of (local) users)

## System Prerequisites

```bash
sudo apt-get update && sudo apt-get upgrade -y
sudo add-apt-repository ppa:ondrej/php -y
sudo apt-get install git redis-server postgresql postgresql-contrib nginx php8.2-common php8.2-fpm php8.2-cli php8.2-amqp php8.2-pgsql php8.2-gd php8.2-curl php8.2-simplexml php8.2-dom php8.2-xml php8.2-redis php8.2-mbstring php8.2-intl unzip -y
sudo curl -sS https://getcomposer.org/installer -o /tmp/composer-setup.php
sudo php /tmp/composer-setup.php --install-dir=/usr/local/bin --filename=composer
```

## Firewall

```bash
// todo
```

## Install Node.js & Yarn (frontend tools)

```bash
curl -sL https://deb.nodesource.com/setup_16.x | sudo bash -
# Or use NodeJS LTS
# curl -sL https://deb.nodesource.com/setup_lts.x | sudo bash -

curl -sL https://dl.yarnpkg.com/debian/pubkey.gpg | gpg --dearmor | sudo tee /usr/share/keyrings/yarnkey.gpg >/dev/null
echo "deb [signed-by=/usr/share/keyrings/yarnkey.gpg] https://dl.yarnpkg.com/debian stable main" | sudo tee /etc/apt/sources.list.d/yarn.list
sudo apt-get update && sudo apt-get install nodejs yarn
```

## Create new user

```bash
sudo adduser kbin
sudo usermod -aG sudo kbin
sudo usermod -aG www-data kbin
sudo su - kbin
```

## Create folder

```bash
sudo mkdir -p /var/www/kbin
sudo chown kbin:www-data /var/www/kbin
```

## Generate Secrets

> **Note**
> This will generate several valid tokens for the kbin setup, you will need quite a few.

```bash
for counter in {1..2}; do node -e "console.log(require('crypto').randomBytes(16).toString('hex'))"; done && for counter in {1..3}; do node -e "console.log(require('crypto').randomBytes(32).toString('hex'))"; done
```

## First setup steps

### Clone git repository

```bash
cd /var/www/kbin
git clone https://codeberg.org/Kbin/kbin-core.git .
```

### Configure `public/media` folder

```bash
mkdir public/media
sudo chmod -R 777 public/media
sudo chown -R kbin:www-data public/media
```

### Configure `var` folder

Create & set permissions to the `var` directory:

```bash
cd /var/www/kbin
mkdir var

# See also: https://symfony.com/doc/current/setup/file_permissions.html
# if the following commands don't work, try adding `-n` option to `setfacl`
HTTPDUSER=$(ps axo user,comm | grep -E '[a]pache|[h]ttpd|[_]www|[w]ww-data|[n]ginx' | grep -v root | head -1 | cut -d\  -f1)

# Set permissions for future files and folders
sudo setfacl -dR -m u:"$HTTPDUSER":rwX -m u:$(whoami):rwX var

# Set permissions on the existing files and folders
sudo setfacl -R -m u:"$HTTPDUSER":rwX -m u:$(whoami):rwX var
```

### The `.env` file

Make a copy of the `.env.example_v2` and edit the `.env` configure file:

```
cp .env.example_v2 .env
nano .env
```

Make sure you have substituted all the passwords and configured the basic services in the `.env` file.

> **Note**
> The snippet below are to variables inside the .env file. Using the keys generated in the section above "Generating Secrets" fill in the values. You should fully review this file to ensure everything is configured correctly.

```conf
REDIS_PASSWORD="{!SECRET!!KEY!-32_1-!}"
APP_SECRET="{!SECRET!!KEY-16_1-!}"
POSTGRES_PASSWORD={!SECRET!!KEY!-32_2-!}
RABBITMQ_PASSWORD="{!SECRET!!KEY!-16_2-!}"
MERCURE_JWT_SECRET="{!SECRET!!KEY!-32_3-!}"
```

Other important `.env` configs:

```conf
# Configure your media URL correctly:
KBIN_STORAGE_URL=https://domain.tld/media

# Ubuntu installs PostgreSQL v14 by default
POSTGRES_VERSION=14

# Configure email, eg. using SMTP
MAILER_DSN=smtp://localhost:25?encryption=ssl&auth_mode=login&username=&password=
# But if already have Postfix configured, just use:
MAILER_DSN=sendmail://default
# Or Gmail
MAILER_DSN=gmail://username:password@localhost?encryption=tls&auth_mode=oauth

# Mercure (assuming you are using Mercure Caddy on port 3000)
MERCURE_HOST=localhost:3000
MERCURE_URL=http://${MERCURE_HOST}/.well-known/mercure
MERCURE_PUBLIC_URL=https://${KBIN_DOMAIN}/.well-known/mercure
```

## Service Configuration

### PHP

Edit some PHP settings within your `php.ini` file:

```bash
sudo nano /etc/php/8.2/fpm/php.ini
```

```ini
; Both max file size and post body size are personal preferences
upload_max_filesize = 8M
post_max_size = 8M
; Remember the memory limit is per child process
memory_limit = 256M
```

Optionally also enable OPCache for improved performances with PHP:

```ini
opcache.enable=1
opcache.enable_cli=1
; Memory consumption (in MBs), personal preference
opcache.memory_consumption=512
; Internal string buffer (in MBs), personal preference
opcache.interned_strings_buffer=128
opcache.max_accelerated_files=100000
; Enable PHP JIT
opcache.jit_buffer_size=500M
```

Edit your PHP `www.conf` file as well, to increase the amount of PHP child processes (optional):

```bash
sudo nano /etc/php/8.2/fpm/pool.d/www.conf
```

With the content (these are personal preferences, adjust to your needs):

```conf
pm = dynamic
pm.max_children = 60
pm.start_servers = 10
pm.min_spare_servers = 5
pm.max_spare_servers = 10
```

Be sure to restart (or reload) the PHP-FPM service after you applied any changing to the `php.ini` file:

```bash
sudo systemctl restart php8.2-fpm.service
```

### Composer

Choose either production or developer (not both).

#### Composer Production

```bash
composer install --no-dev
composer dump-env prod
APP_ENV=prod APP_DEBUG=0 php bin/console cache:clear
composer clear-cache
```

#### Composer Development

If you run production already, then _skip the steps below_.

```bash
composer install
composer dump-env dev
APP_ENV=dev APP_DEBUG=1 php bin/console cache:clear
composer clear-cache
```

### Redis

Edit `redis.conf` file:

```bash
sudo nano /etc/redis/redis.conf

# Search on (ctrl + w): requirepass foobared
# Remove the #, change foobared to the new {!SECRET!!KEY!-32_1-!} password, generated earlier

# Search on (ctrl + w): supervised no
# Change no to systemd, considering Ubuntu is using systemd
```

Save and exit (CTRL+x) the file.

Restart Redis:

```bash
sudo systemctl restart redis.service
```

Within your `.env` file, change the Redis host to `127.0.0.1` (localhost), proper IP or use socket file:

```conf
REDIS_HOST=127.0.0.1:6379
REDIS_PASSWORD={!SECRET!!KEY!-32_1-!}
REDIS_DNS=redis://${REDIS_PASSWORD}@${REDIS_HOST}

# Or if you want to use socket file:
#REDIS_DNS=redis://${REDIS_PASSWORD}/var/run/redis/redis-server.sock
```

### PostgreSQL (Database)

Create a new `kbin` database user, using the password, `{!SECRET!!KEY!-32_2-!}`, you generated earlier:

```bash
sudo -u postgres createuser --createdb --createrole --pwprompt kbin
```

Create tables and database structure:

```bash
cd /var/www/kbin
php bin/console doctrine:database:create
php bin/console doctrine:migrations:migrate
```

### Yarn

```bash
cd /var/www/kbin
yarn # Installs all NPM dependencies
yarn build # Builds frontend
```

Make sure you have substituted all the passwords and configured the basic services.

### NGINX

We will use NGINX as a reverse proxy between the public site and various backend services (static files, PHP and Mercure).

#### General NGINX configs

Generate DH parameters (will be used later):

```bash
sudo openssl dhparam -dsaparam -out /etc/nginx/dhparam.pem 4096
```

Set the correct permissions:

```bash
sudo chmod 644 /etc/nginx/dhparam.pem
```

Edit the main NGINX config file: `sudo nano /etc/nginx/nginx.conf` with the following content within the `http {}` section (replace when needed):

```conf
ssl_protocols TLSv1.2 TLSv1.3; # Requires nginx >= 1.13.0 else only use TLSv1.2
ssl_prefer_server_ciphers on;
ssl_dhparam /etc/nginx/dhparam.pem;
ssl_ciphers ECDHE-ECDSA-AES128-GCM-SHA256:ECDHE-RSA-AES128-GCM-SHA256:ECDHE-ECDSA-AES256-GCM-SHA384:ECDHE-RSA-AES256-GCM-SHA384:ECDHE-ECDSA-CHACHA20-POLY1305:ECDHE-RSA-CHACHA20-POLY1305:DHE-RSA-AES128-GCM-SHA256:DHE-RSA-AES256-GCM-SHA384:DHE-RSA-CHACHA20-POLY1305;
ssl_prefer_server_ciphers off;
ssl_ecdh_curve secp521r1:secp384r1:secp256k1; # Requires nginx >= 1.1.0

ssl_session_timeout 1d;
ssl_session_cache shared:MozSSL:10m;  # about 40000 sessions
ssl_session_tickets off; # Requires nginx >= 1.5.9

ssl_stapling on; # Requires nginx >= 1.3.7
ssl_stapling_verify on; # Requires nginx => 1.3.7

# This is an example DNS (replace the DNS IPs if you wish)
resolver 1.1.1.1 9.9.9.9 valid=300s;
resolver_timeout 5s;

# Gzip compression
gzip            on;
gzip_disable    msie6;

gzip_vary       on;
gzip_comp_level 3;
gzip_min_length 256;
gzip_buffers    16 8k;
gzip_proxied    any;
gzip_types
        text/css
        text/plain
        text/javascript
        text/cache-manifest
        text/vcard
        text/vnd.rim.location.xloc
        text/vtt
        text/x-component
        text/x-cross-domain-policy
        application/javascript
        application/json
        application/x-javascript
        application/ld+json
        application/xml
        application/xml+rss
        application/xhtml+xml
        application/x-font-ttf
        application/x-font-opentype
        application/vnd.ms-fontobject
        application/manifest+json
        application/rss+xml
        application/atom_xml
        application/vnd.geo+json
        application/x-web-app-manifest+json
        image/svg+xml
        image/x-icon
        image/bmp
        font/opentype;
```

#### Kbin Server Block

```bash
sudo nano /etc/nginx/sites-available/kbin.conf
```

Content of `kbin.conf`:

```kbin.conf
# Redirect HTTP to HTTPS
server {
    server_name domain.tld www.domain.tld;
    listen 80;

    return 301 https://$host$request_uri;
}

server {
    listen 443 ssl http2;
    server_name domain.tld www.domain.tld;

    root /var/www/kbin/public;

    index index.php;

    charset utf-8;

    # TLS
    ssl_certificate /etc/letsencrypt/live/domain.tld/fullchain.pem;
    ssl_certificate_key /etc/letsencrypt/live/domain.tld/privkey.pem;

    # Don't leak powered-by
    fastcgi_hide_header X-Powered-By;

    # Security headers
    add_header X-Frame-Options "DENY" always;
    add_header X-XSS-Protection "1; mode=block" always;
    add_header X-Content-Type-Options "nosniff" always;
    add_header Referrer-Policy "no-referrer" always;
    add_header X-Download-Options "noopen" always;
    add_header X-Permitted-Cross-Domain-Policies "none" always;
    add_header Strict-Transport-Security "max-age=31536000; includeSubDomains; preload" always;

    client_max_body_size 20M; # Max size of a file that a user can upload

    # Logs
    error_log /var/log/nginx/kbin_error.log;
    access_log /var/log/nginx/kbin_access.log;

    location / {
        # try to serve file directly, fallback to app.php
        try_files $uri /index.php$is_args$args;
    }

    location = /favicon.ico { access_log off; log_not_found off; }
    location = /robots.txt  { access_log off; log_not_found off; }

    location /.well-known/mercure {
        proxy_pass http://127.0.0.1:3000$request_uri;
        proxy_read_timeout 24h;
        proxy_http_version 1.1;
        proxy_set_header Connection "";

        proxy_set_header X-Forwarded-For $proxy_add_x_forwarded_for;
        proxy_set_header X-Forwarded-Host $host;
        proxy_set_header X-Forwarded-Proto $scheme;
    }

    location ~ ^/index\.php(/|$) {
        default_type application/x-httpd-php;
        fastcgi_pass unix:/var/run/php/php-fpm.sock;
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


    location ~ /\.(?!well-known).* {
        deny all;
    }

    # return 404 for all other php files not matching the front controller
    # this prevents access to other php files you don't want to be accessible.
    location ~ \.php$ {
        return 404;
    }
}
```

Enable the NGINX site, using a symlink:

```bash
sudo ln -s /etc/nginx/sites-available/kbin.conf /etc/nginx/sites-enabled/
```

Restart (or reload) NGINX:

```bash
sudo systemctl restart nginx
```

#### Let's Encrypt (TLS)

> **Note**
> This is installed via snap to reduce system dependencies ran, and the preferred way. Run in standalone mode to not mess with the default config and minimize errors all around. If you prefer no snaps you can install other ways though, however is the preferred way to install from let's encrypt.

Install Snapd:

```bash
sudo apt-get install snapd
```

Install Certbot:

```bash
sudo snap install core; sudo snap refresh core
sudo snap install --classic certbot
```

Add symlink:

```bash
sudo ln -s /snap/bin/certbot /usr/bin/certbot
```

Generate a TLS certificate for your domain(s):

```
sudo certbot certonly --standalone -d domain.tld -d www.domain.tld

# Or if you wish not to use the standalone mode:
#sudo certbot --nginx -d domain.tld -d www.domain.tld
```

### Symfony Messenger (Queues)

#### Install RabbitMQ (Recommended, but optional)

[RabbitMQ Install](https://www.rabbitmq.com/install-debian.html#apt-quick-start-cloudsmith)

```bash
sudo apt-get install curl gnupg apt-transport-https -y

## Team RabbitMQ's main signing key
curl -1sLf "https://keys.openpgp.org/vks/v1/by-fingerprint/0A9AF2115F4687BD29803A206B73A36E6026DFCA" | sudo gpg --dearmor | sudo tee /usr/share/keyrings/com.rabbitmq.team.gpg > /dev/null
## Community mirror of Cloudsmith: modern Erlang repository
curl -1sLf https://ppa1.novemberain.com/gpg.E495BB49CC4BBE5B.key | sudo gpg --dearmor | sudo tee /usr/share/keyrings/rabbitmq.E495BB49CC4BBE5B.gpg > /dev/null
## Community mirror of Cloudsmith: RabbitMQ repository
curl -1sLf https://ppa1.novemberain.com/gpg.9F4587F226208342.key | sudo gpg --dearmor | sudo tee /usr/share/keyrings/rabbitmq.9F4587F226208342.gpg > /dev/null

## Add apt repositories maintained by Team RabbitMQ
sudo tee /etc/apt/sources.list.d/rabbitmq.list <<EOF
## Provides modern Erlang/OTP releases
##
deb [signed-by=/usr/share/keyrings/rabbitmq.E495BB49CC4BBE5B.gpg] https://ppa1.novemberain.com/rabbitmq/rabbitmq-erlang/deb/ubuntu jammy main
deb-src [signed-by=/usr/share/keyrings/rabbitmq.E495BB49CC4BBE5B.gpg] https://ppa1.novemberain.com/rabbitmq/rabbitmq-erlang/deb/ubuntu jammy main

## Provides RabbitMQ
##
deb [signed-by=/usr/share/keyrings/rabbitmq.9F4587F226208342.gpg] https://ppa1.novemberain.com/rabbitmq/rabbitmq-server/deb/ubuntu jammy main
deb-src [signed-by=/usr/share/keyrings/rabbitmq.9F4587F226208342.gpg] https://ppa1.novemberain.com/rabbitmq/rabbitmq-server/deb/ubuntu jammy main
EOF

## Update package indices
sudo apt-get update -y

## Install Erlang packages
sudo apt-get install -y erlang-base \
                        erlang-asn1 erlang-crypto erlang-eldap erlang-ftp erlang-inets \
                        erlang-mnesia erlang-os-mon erlang-parsetools erlang-public-key \
                        erlang-runtime-tools erlang-snmp erlang-ssl \
                        erlang-syntax-tools erlang-tftp erlang-tools erlang-xmerl

## Install rabbitmq-server and its dependencies
sudo apt-get install rabbitmq-server -y --fix-missing
```

Now, we will add a new `kbin` user with the correct permissions:

```bash
sudo rabbitmqctl add_user 'kbin' '{!SECRET!!KEY!-16_2-!}'
sudo rabbitmqctl set_permissions -p '/' 'kbin' '.' '.' '.*'
```

Remove the `guest` account:

```bash
sudo rabbitmqctl delete_user 'guest'
```

#### Configure Queue Messenger Handler

```bash
cd /var/www/kbin
nano .env
```

```conf
# Use RabbitMQ (recommended):
RABBITMQ_HOST=127.0.0.1:5672
RABBITMQ_PASSWORD=!ChangeThisRabbitPass!
MESSENGER_TRANSPORT_DSN=amqp://kbin:${RABBITMQ_PASSWORD}@${RABBITMQ_HOST}/%2f/messages
# or Redis:
MESSENGER_TRANSPORT_DSN=redis://${REDIS_PASSWORD}@${REDIS_HOST}/messages
# or database:
MESSENGER_TRANSPORT_DSN=doctrine://default
```

### Mercure

[Mercure Website](https://mercure.rocks/)

> Visit https://caddyserver.com/download?package=github.com%2Fdunglas%2Fmercure%2Fcaddy
> Select your server architecture from the drop down list
> Mercure is selected here. You _do need_ to select your server's architecture if it differs
> Copy the download button's link.

Download and install Mercure:

```bash
sudo wget "https://caddyserver.com/api/download?os=linux&arch=amd64&p=github.com%2Fdunglas%2Fmercure%2Fcaddy&idempotency=51465666707202" -O /usr/local/bin/mercure

sudo chmod +x /usr/local/bin/mercure
```

Prepare folder structure:

```bash
cd /var/www/kbin
mkdir -p metal/caddy
```

[Caddyfile Global Options](https://caddyserver.com/docs/caddyfile/options)

> **Note**
> Caddyfiles: The one provided should work for most people, edit as needed via the previous link. Combination of mercure.conf and Caddyfile

Add new `Caddyfile` file:

```bash
nano metal/caddy/Caddyfile
```

The content of the `Caddyfile`:

```
{
        {$GLOBAL_OPTIONS}
        auto_https off
        http_port {$HTTP_PORT}
        persist_config off
        log {
                output file /var/www/kbin/var/log/mercure.log
                # DEBUG, INFO, WARN, ERROR, PANIC, and FATAL
                level WARN
                format filter {
                        wrap console
                        fields {
                                uri query {
                                        replace authorization REDACTED
                                }
                        }
                }
        }
}

{$SERVER_NAME:localhost}

{$EXTRA_DIRECTIVES}

route {
 mercure {
  # Transport to use (default to Bolt)
  transport_url {$MERCURE_TRANSPORT_URL:bolt://mercure.db}
  # Publisher JWT key
  publisher_jwt {env.MERCURE_PUBLISHER_JWT_KEY} {env.MERCURE_PUBLISHER_JWT_ALG}
  # Subscriber JWT key
  subscriber_jwt {env.MERCURE_SUBSCRIBER_JWT_KEY} {env.MERCURE_SUBSCRIBER_JWT_ALG}
    # Workaround for now
  anonymous
  # Extra directives
  {$MERCURE_EXTRA_DIRECTIVES}
 }

 respond /healthz 200
 respond "Not Found" 404
}
```

Ensure not random formatting errors in the Caddyfile

```bash
mercure fmt metal/caddy/Caddyfile --overwrite
```

Mercure will be configured further in the next section (Supervisor).

### Setup Supervisor

```bash
sudo apt-get install supervisor
```

Configure the messenger jobs:

```bash
sudo nano /etc/supervisor/conf.d/messenger-worker.conf
```

With the following content:

```conf
[program:messenger-kbin]
command=php /var/www/kbin/bin/console messenger:consume async --time-limit=1800
user=www-data
numprocs=2
startsecs=0
autostart=true
autorestart=true
startretries=10
process_name=%(program_name)s_%(process_num)02d

[program:messenger-ap]
command=php /var/www/kbin/bin/console messenger:consume async_ap --time-limit=1800
user=www-data
numprocs=2
startsecs=0
autostart=true
autorestart=true
startretries=10
process_name=%(program_name)s_%(process_num)02d
```

Save and close the file.

We also use a supervisor for running Mercure job:

```bash
sudo nano /etc/supervisor/conf.d/mercure.conf
```

With the following content:

```conf
[program:mercure]
command=/usr/local/bin/mercure run --config /var/www/kbin/metal/caddy/Caddyfile
process_name=%(program_name)s_%(process_num)s
numprocs=1
environment=MERCURE_PUBLISHER_JWT_KEY="{!SECRET!!KEY!-32_3-!}",MERCURE_SUBSCRIBER_JWT_KEY="{!SECRET!!KEY!-32_3-!}",SERVER_NAME=":3000",HTTP_PORT="3000"
directory=/var/www/kbin/metal/caddy
autostart=true
autorestart=true
startsecs=5
startretries=10
user=www-data
redirect_stderr=false
stdout_syslog=true
```

Save and close the file. Restart supervisor jobs:

```bash
sudo supervisorctl reread && sudo supervisorctl update && sudo supervisorctl start all
```

_Hint:_ If you wish to restart your supervisor jobs in the future, use:

```bash
sudo supervisorctl restart all
```

### Kbin first setup

To create a new admin user (without email verification), please change the `username`, `email` and `password` below:

```bash
php bin/console kbin:user:create <username> <email@example.com> <password>
php bin/console kbin:user:admin <username>
```

```bash
php bin/console kbin:ap:keys:update
```

Next, log in and create a magazine named “random” to which unclassified content from the Fediverse will flow.

### Upgrades

If you perform a kbin upgrade (e.g., `git pull`), be aware to _always_ execute the following Bash script:

```bash
./bin/post-upgrade
```

And when needed, also execute: `sudo redis-cli FLUSHDB` to get rid of Redis cache issues. And reload the PHP FPM service if you have OPCache enabled.

### Backup and restore

```bash
PGPASSWORD="YOUR_PASSWORD" pg_dump -U kbin kbin > dump.sql
psql -U kbin kbin < dump.sql
```

### Logs

RabbitMQ:

- `sudo tail -f /var/log/rabbitmq/rabbit@*.log`

Supervisor:

- `sudo tail -f /var/log/supervisor/supervisord.log`

Supervisor jobs (Mercure and Messenger):

- `sudo tail -f /var/log/supervisor/mercure*.log`
- `sudo tail -f /var/log/supervisor/messenger-ap*.log`
- `sudo tail -f /var/log/supervisor/messenger-kbin*.log`

The separate Mercure log:

- `sudo tail -f /var/www/kbin/var/log/mercure.log`

Application Logs (prod or dev logs):

- `tail -f /var/www/kbin/var/log/prod.log`

Or:

- `tail -f /var/www/kbin/var/log/dev.log`

Web-server (Nginx):

- `sudo tail -f /var/log/nginx/kbin_access.log`
- `sudo tail -f /var/log/nginx/kbin_error.log`

### Debugging

**Please check the logs above first.** If you are really stuck, visit to our [Matrix space](https://matrix.to/#/%23kbin-space:matrix.org), there are dedicated rooms for 'Getting Started', 'Server Owners' and 'Issues'.

Test PostgreSQL connections if using a remote server, same with Redis. Ensure no firewall rules blocking are any incoming or out-coming traffic (e.g., port on 80 and 443).

### S3 Images storage (optional)

Edit your `.env` file:

```conf
S3_KEY=
S3_SECRET=
S3_BUCKET=media.karab.in
S3_REGION=eu-central-1
S3_ENDPOINT=
S3_VERSION=latest
```

And then edit the: `config/packages/oneup_flysystem.yaml` file:

```yaml
oneup_flysystem:
  adapters:
    default_adapter:
      local:
        location: "%kernel.project_dir%/public/media"

    kbin.s3_adapter:
      awss3v3:
        client: kbin.s3_client
        bucket: "%amazon.s3.bucket%"

  filesystems:
    public_uploads_filesystem:
      adapter: kbin.s3_adapter
      alias: League\Flysystem\Filesystem
```

```yaml
// todo thumbnails
```

### Captcha (optional)

Go to [hcaptcha.com](https://www.hcaptcha.com) and create a free account. Make a site key and a secret. Add domain.tld to the site key.

Edit your `.env` file:

```conf
KBIN_CAPTCHA_ENABLED=true
HCAPTCHA_SITE_KEY=sitekey
HCAPTCHA_SECRET=secret
```

```
composer dump-env prod
```
or
```
composer dump-env dev
```

Go to the admin panel, then to the settings tab and check “Captcha enabled” and press “Save”.

## Performance hints

- [Resolve cache images in the background](https://symfony.com/bundles/LiipImagineBundle/current/optimizations/resolve-cache-images-in-background.html#symfony-messenger)

## References

- [https://symfony.com/doc/current/setup.html](https://symfony.com/doc/current/setup.html)
- [https://symfony.com/doc/current/deployment.html](https://symfony.com/doc/current/deployment.html)
- [https://symfony.com/doc/current/setup/web_server_configuration.html](https://symfony.com/doc/current/setup/web_server_configuration.html)
- [https://symfony.com/doc/current/messenger.html#deploying-to-production](https://symfony.com/doc/current/messenger.html#deploying-to-production)
- [https://codingstories.net/how-to/how-to-install-and-use-mercure/](https://codingstories.net/how-to/how-to-install-and-use-mercure/)

