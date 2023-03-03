#!/bin/bash
set -ex

# Hotfix for M1
source /etc/bash.vips-arm64-hotfix.sh

composer install

./flow doctrine:migrate

# only run site import when nothing was imported before
importedSites=`./flow site:list`
if [ "$importedSites" = "No sites available" ]; then
    echo "Importing content from ./ContentDump"
    ./ContentDump/importSite.sh
fi

./flow resource:publish
./flow flow:cache:flush
./flow cache:warmup

# e2e test
./flow behat:setup
rm bin/selenium-server.jar # we do not need this

# start nginx in background
nginx &

# start PHP-FPM
exec /usr/local/sbin/php-fpm
