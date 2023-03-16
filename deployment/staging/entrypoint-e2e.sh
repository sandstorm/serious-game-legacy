#!/bin/bash
set -ex

green_echo() {
  printf "\033[0;32m%s\033[0m\n" "${1}"
}

red_echo() {
  printf "\033[0;31m%s\033[0m\n" "${1}"
}

cd /app

green_echo "####### Running Flow Commands with context '$FLOW_CONTEXT'"
green_echo "DB - host: $DB_NEOS_HOST, port: $DB_NEOS_PORT, user: $DB_NEOS_USER, database: $DB_NEOS_DATABASE"
./flow doctrine:migrate
./flow resource:publish
./flow flow:cache:flush
./flow cache:warmup

green_echo "####### Symlinking e2e nginx conf"

envsubst '${SANDSTORM_MAPS_API_KEY}' < /etc/nginx/nginx.conf > /tmp/nginx.conf && mv /tmp/nginx.conf /etc/nginx/nginx.conf
ln -s /etc/nginx/nginx-e2etest-server-prod.conf /etc/nginx/conf.d/nginx-e2etest-server-prod.conf

green_echo "####### Starting nginx"
nginx &

green_echo "####### Starting php-fpm"
exec /usr/local/sbin/php-fpm &

export PLAYWRIGHT_API_URL=http://e2e-testrunner:3000
export SYSTEM_UNDER_TEST_URL_FOR_PLAYWRIGHT=http://$(hostname -i):9090

green_echo "####### Waiting for Neos (System under testing) to start"

# We use a counter here so that we can stop the pipeline and not have it run
# forever.
counter=0
# we call the Neos login page, because calling the frontend starting page without a site would respond a 500
until $(curl --output /dev/null --silent --fail http://127.0.0.1:9090/neos/login); do
    if [ "$counter" -gt 4 ]; then
        red_echo "FAILED: Waiting for Neos to start took too long!!!"
        exit 1
    fi
    printf '.'
    counter=$((counter+1))
    sleep 5
done

green_echo "------> SUCCESS starting Neos"
green_echo "####### Running Tests"

cd /app && rm -Rf e2e-results && mkdir e2e-results && bin/behat --format junit --out e2e-results --format pretty --out std -c Packages/Sites/MyVendor.AwesomeNeosProject/Tests/Behavior/behat.yml.dist

green_echo "------> SUCCESS finished running tests"

cp -R /app/e2e-results $CI_PROJECT_DIR/e2e-results
cp -R /app/Web/styleguide $CI_PROJECT_DIR/styleguide || true

green_echo "ALL DONE"
