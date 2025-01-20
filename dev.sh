#!/bin/bash
############################## DEV_SCRIPT_MARKER ##############################
# This script is used to document and run recurring tasks in development.     #
#                                                                             #
# You can run your tasks using the script `./dev some-task`.                  #
# You can install the Sandstorm Dev Script Runner and run your tasks from any #
# nested folder using `dev some-task`.                                        #
# https://github.com/sandstorm/Sandstorm.DevScriptRunner                      #
###############################################################################

source ./dev_utilities.sh

set -e

######### TASKS #########

# IMPORTANT: When adding tasks stick to the following naming conventions
#   * use `run-something` for tasks that terminate on their own
#   * use `start-something` for tasks that will run until terminated manually
#     e.g. by pressing ctrl+c or closing the terminal

# Removes docker containers and their data
function nuke {
  # we currently only nuke containers
  _echo_green "Nuking docker containers"
	docker compose down --rmi all --volumes --remove-orphans
}

# Initial project setup
function setup {
  _echo_green "Setting up git lfs"
  git lfs install || true
  git lfs pull || true

  _echo_green "Installing Dev Script Runner"
  brew install sandstorm/tap/dev-script-runner
  brew upgrade sandstorm/tap/dev-script-runner

  brew install yq

  # Adding folders -> when using `docker compose` instead of `docker-compose`
  # folders must already be present
  _echo_green "Creating cache folders"
	mkdir -p ./tmp/composer_cache
	mkdir -p ./tmp/.yarn-cache

	_echo_green "Running initial build"
	build

	# Running composer to install dependencies locally so you have autocompletion
	# in your IDE
	pushd app
	composer install --ignore-platform-reqs
	popd
}

# Execute PHPStan locally
function php-stan() {
  cd app
  docker run -v "$(pwd)":/app composer:2 bash -c \
    'cd /app && composer install --ignore-platform-reqs  && ./bin/phpstan analyse --memory-limit 1G'
}

function php-stan-json() {
  cd app
  docker run -v "$(pwd)":/app composer:2 bash -c \
    'cd /app && composer install --ignore-platform-reqs  && ./bin/phpstan analyse --error-format=prettyJson --memory-limit 1G'
}

######################## Useful Docker Aliases ########################

function start {
    build
    # create external volume "yarn-cache" and "neos-composer-cache" if not existing
    # wont be removed on down and volume removal
    docker volume create --name yarn-cache
    docker volume create --name laravel-composer-cache
	docker compose up -d
}

function build {
	docker compose build
}

function stop {
	docker compose stop
}

function down {
	docker compose down -v --remove-orphans
}

######################### Enter Containers #########################

# Enter neos container via bash
function enter-laravel {
	docker compose exec laravel /bin/bash
}

# Enter db container via bash
function enter-db {
	docker compose exec maria-db /bin/bash
}

# Enter node container via bash
function enter-assets {
	docker compose exec laravel-assets /bin/bash
}

######################### Logs #########################

# All logs
function logs {
	docker compose logs -f "$@"
}

# DB logs
function logs-db {
	docker compose logs -f maria-db
}

# Larvel logs
function logs-laravel {
	docker compose exec -it laravel /bin/bash -c 'tail -f  /app/storage/logs/laravel-*.log'
}

# SCSS/Js compiler logs
function logs-assets {
	docker compose logs -f neos-assets
}

# Flow exceptions
function logs-exceptions {
  _echo_red "TODO IMPLEMENT ME"
	#docker compose exec neos ./watchAndLogExceptions.sh
}

# Show running containers
function ps {
	docker compose ps --all
}

######################### Open Urls in Browser / UIs #########################

# Open site and site/neos in browser
function open-site {
	open http://127.0.0.1:8090/admin
}

# Open local db with your default UI
function open-local-db {
	open "mysql://laravel:laravel@localhost:13306/laravel"
}

######################### Laravel / Filament Specifics #########################

function setup-laravel-autocomplete() {
  docker compose exec laravel ./artisan  ide-helper:models --filename _ide_helper_models.php  -n
  docker compose exec laravel ./artisan  ide-helper:generate
  docker compose exec laravel ./artisan  ide-helper:meta
}

# run Laravel Pint Code Style fixer
function pint() {
  docker compose exec laravel /app/vendor/bin/pint --config /app/.pint.json $@
}

# run PHPSTAN
function phpstan() {
  docker compose exec laravel /app/vendor/bin/phpstan $@
}

# run Pest (Unit Testing)
function pest() {
  docker compose exec -e DB_DATABASE=laravel_testing -it laravel /app/vendor/bin/pest $@
}

function artisan() {
  docker compose exec laravel /app/artisan $@
}

# render the "application is unavailable" page, shown during deployments
function render-application-unavailable() {
  mkdir -p ingress-caddy-proxy/error-pages
  docker compose exec laravel /app/artisan app:render-application-unavailable > ingress-caddy-proxy/error-pages/application-unavailable.html
}

function composer-update-filament-advanced-tables() {
  cd app

  composer remove archilex/filament-filter-sets

  composer config repositories.advancedtables composer https://filament-filter-sets.composer.sh
  _echo_yellow "Now, go to **Filament Advanced Tables TEAM Lizenz** in Bitwarden and open the checkout.anystack.sh URL. Enter Username + Password (Project Specific)"
  composer require archilex/filament-filter-sets
  composer show -- archilex/filament-filter-sets > DistributionPackages/archilex-filament-filter-sets.md

  rm -Rf DistributionPackages/archilex-filament-filter-sets

  cp -R vendor/archilex/filament-filter-sets DistributionPackages/archilex-filament-filter-sets
  composer config --unset repositories.advancedtables
  composer require archilex/filament-filter-sets @dev
}

function composer-update-filament-record-finder-pro() {
  cd app

  composer remove ralphjsmit/laravel-filament-record-finder

  composer config repositories.laravelrecordfinder composer https://satis.ralphjsmit.com
  _echo_yellow "Now, go to **Filament Record Finder Pro TEAM Lizenz** in Bitwarden - enter Username + Password if prompted (always the same for all projects)"
  composer require ralphjsmit/laravel-filament-record-finder
  composer show -- ralphjsmit/laravel-filament-record-finder > DistributionPackages/ralphjsmit-laravel-filament-record-finder.md
  composer show -- ralphjsmit/packages > DistributionPackages/ralphjsmit-packages.md

  rm -Rf DistributionPackages/ralphjsmit-laravel-filament-record-finder
  rm -Rf DistributionPackages/ralphjsmit-packages

  cp -R vendor/ralphjsmit/laravel-filament-record-finder DistributionPackages/ralphjsmit-laravel-filament-record-finder
  cp -R vendor/ralphjsmit/packages DistributionPackages/ralphjsmit-packages
  composer config --unset repositories.laravelrecordfinder
  composer require ralphjsmit/packages "dev-main as 1.4.2" ralphjsmit/laravel-filament-record-finder @dev
}
_echo_green "------------- Running task $@ -------------"

# THIS NEEDS TO BE LAST!!!
# this will run your tasks
"$@"
