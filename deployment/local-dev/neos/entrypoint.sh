#!/bin/bash
set -ex

# Hotfix for M1
source /etc/bash.vips-arm64-hotfix.sh

mkdir -p /var/www/.composer || true
composer config --global cache-dir /composer_cache
composer config --global 'preferred-install.sandstorm/*' source

composer install

./flow flow:cache:flush

./flow doctrine:migrate

# only run site import when nothing was imported before
importedSites=`./flow site:list`
if [ "$importedSites" = "No sites available" ]; then
    echo "Importing content from ./ContentDump"
    ./ContentDump/importSite.sh
fi

./flow user:create --roles Administrator $ADMIN_USERNAME $ADMIN_PASSWORD LocalDev Admin || true

./flow resource:publish
./flow cache:warmup

# e2e test
echo "DUMMY_FILE to prevent download of real selenium server" > bin/selenium-server.jar
./flow behat:setup
rm bin/selenium-server.jar # we do not need this

# replace env variable and start nginx in background
envsubst '${SANDSTORM_MAPS_API_KEY}' < /etc/nginx/nginx.conf > /tmp/nginx.conf && mv /tmp/nginx.conf /etc/nginx/nginx.conf
nginx &

# start PHP-FPM
exec /usr/local/sbin/php-fpm
