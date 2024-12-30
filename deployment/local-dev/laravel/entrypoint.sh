#!/bin/bash
set -ex

# Hotfix for M1
source /etc/bash.vips-arm64-hotfix.sh

mkdir -p /var/www/.composer || true
composer config --global cache-dir /composer_cache
# install composer dependencies for sandstorm packages from source (cool for development of sandstorm packages)
composer config --global 'preferred-install.sandstorm/*' source

composer install

./artisan migrate --force --seed

#envsubst '${SANDSTORM_MAPS_API_KEY}' < /etc/nginx/nginx.conf > /tmp/nginx.conf && mv /tmp/nginx.conf /etc/nginx/nginx.conf
nginx &

# start PHP-FPM
exec /usr/local/sbin/php-fpm
