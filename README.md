# Converter Test Project
Technologies and tools used:
 * [Zend Framework v1.12.20](https://framework.zend.com/downloads/archives) as main framework
 * [jQuery v1.9.1](https://jquery.com/) as helpful JS library
 * [GIT] - commit to your local repository

Server stack:
 * Ubuntu 16.04
 * NGINX
 * MariaDB
 * PHP-fpm

Additional tools:
 * [Chrome DevTools](https://developer.chrome.com/devtools)
 * [Lighthouse](https://github.com/GoogleChrome/lighthouse)
 * [Currencylayer](https://currencylayer.com) - Free website to getting real and actual rates

# Installation guide
- Clone repository:
```
git clone https://github.com/y-harbolinsky/converter.git
```
- Create new database named `currency`, import sql from `currency.sql`
- Configure appropriate settings for database in `application.ini` file
- Setup virtual host. Virtual host example:
```
server {
  listen      80;
  server_name converter.local.com;

  root        /var/www/converter/public;
  index       index.html index.htm index.php;

  location / {
    try_files $uri $uri/ /index.php$is_args$args;
  }

  location ~ \.php$ {
    fastcgi_pass   unix:/run/php/php7.0-fpm.sock;
    fastcgi_param  SCRIPT_FILENAME $document_root$fastcgi_script_name;
    include        fastcgi_params;
  }

  return 301 https://converter.local.com$request_uri;

}

server {
  listen 443 ssl;
  server_name converter.local.com;
  ssl_certificate /etc/nginx/ssl/nginx.crt;
  ssl_certificate_key /etc/nginx/ssl/nginx.key;
  root        /var/www/converter/public;
  index       index.html index.htm index.php;

  location / {
    try_files $uri $uri/ /index.php$is_args$args;
  }

  location ~ \.php$ {
    fastcgi_pass   unix:/run/php/php7.0-fpm.sock;
    fastcgi_param  SCRIPT_FILENAME $document_root$fastcgi_script_name;
    include        fastcgi_params;
  }
}
```
- Add cron job to Ubuntu crontab `0 * * * * /usr/bin/php7.0 -q /var/www/converter/cron/exchangeUpdating.php > /dev/null`
