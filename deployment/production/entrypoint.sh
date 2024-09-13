#!/bin/bash
set -ex

./flow flow:cache:flush

./flow doctrine:migrate

# NOTE: for Staging and PROD, we do not import the content dump here, as usually you want to do it
# in a more controlled way.

# NOTE: for Staging and PROD, we do not create users here, as we want secure users and passwords.

./flow resource:publish
./flow cache:warmup

# If you want to use cron:
# - enable the following 3 lines
# - adjust the TODO_SERVICE_NAME in the glue.cloud.sandstorm.de monitoring call.
#
#echo "curl https://glue.cloud.sandstorm.de/api/public/cronjob-monitor/TODO_SERVICE_NAME" > /app/notify-cronjob.sh
#chmod +x /app/notify-cronjob.sh
#/usr/local/bin/supercronic /crontab &



# replace env variable and start nginx
envsubst '${SANDSTORM_MAPS_API_KEY}' < /etc/nginx/nginx.conf > /tmp/nginx.conf && mv /tmp/nginx.conf /etc/nginx/nginx.conf
nginx &

exec /usr/local/sbin/php-fpm
