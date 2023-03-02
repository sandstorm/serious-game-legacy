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
source "$HOME/.nvm/nvm.sh"

set -e

######### TASKS #########

# IMPORTANT: When adding tasks stick to the following naming conventions
#   * use `run-something` for tasks that terminate on their own
#   * use `start-something` for tasks that will run until terminated manually
#     e.g. by pressing ctrl+c or closing the terminal

# Removes docker containers and their tata
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

  # Adding folders -> when using `docker compose` instead of `docker-compose`
  # folders must already be present
  _echo_green "Creating cache folders"
	mkdir -p ./tmp/composer_cache
	mkdir -p ./tmp/.yarn-cache

	_echo_green "Running initial build"
	build

  _echo_green "Installing Playwright testrunner"
	pushd ./e2e-testrunner
	nvm install
	nvm use
	npm install
	popd

	# Running composer to install dependencies locally so you have autocompletion
	# in your IDE
	pushd app
	composer install --ignore-platform-reqs
	popd
	pushd app/Build/Behat
	composer install --ignore-platform-reqs
	popd
}

######################## Useful Docker Aliases ########################

function start {
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
function enter-neos {
	docker compose exec neos /bin/bash
}

# Enter db container via bash
function enter-db {
	docker compose exec maria-db /bin/bash
}

# Enter node container via bash
function enter-assets {
	docker compose exec neos-assets /bin/bash
}

######################### Logs #########################

function logs {
	docker compose logs -f
}

function logs-db {
	docker compose logs -f maria-db
}

function logs-neos {
	docker compose logs -f neos
}

function logs-assets {
	docker compose logs -f neos-assets
}

function logs-flow-exceptions {
	docker compose exec neos ./watchAndLogExceptions.sh
}

# Show running containers
function ps {
	docker compose ps
}

######################### Open Urls in Browser / UIs #########################

# Open site and site/neos in browser
function open-site {
	open http://127.0.0.1:8081
	open http://127.0.0.1:8081/neos
}

# Open e2e testing site and site/neos in browser
function open-site-e2e {
	open http://127.0.0.1:9090
	open http://127.0.0.1:9090/neos
}

# Open styleguide with component screenshots in browser
function open-styleguide {
	open http://127.0.0.1:9090/styleguide
}

# Open local db with your default UI
function open-local-db {
	open "mysql://neos:neos@localhost:13306/neos"
	open "mysql://neos:neos@localhost:13306/neos_e2etest"
}

######################### Site Export and Import #########################

# TODO: fix dump, then this should not be needed anymore
function site-create {
  docker compose exec neos ./flow site:create --name AwesomeNeosProject --package-key MyVendor.AwesomeNeosProject --node-type MyVendor.AwesomeNeosProject:Document.StartPage
}

# Export site using an SQL dump and zipping resources
function site-export {
  _echo_red "IMPORTANT: This dumb cannot be used as a backup. As we removed all user data"
  _echo_red "and related workspaces to prevent committing sensitive user data."
	docker compose exec neos /app/ContentDump/exportSite.sh
}

function site-export-prod {
  _log_green "Starting prod content dump. This might take some time, depending on the size of the Resource folder."

	NAMESPACE="myvendor-awesomeneosproject-staging"
  # 1) find the right kubernetes pod to connect to
  kubectl >> /dev/null 2>&1
  if [[ $? -gt 0 ]]; then
      echo "kubectl must be installed to run the script"
      exit 10
  fi

  kubectl get namespace $NAMESPACE  >> /dev/null 2>&1
  if [[ $? -gt 0 ]]; then
      echo "Namespace not found!"
      exit 20
  fi

  PODNAME=$(kubectl get pods -n $NAMESPACE --template '{{range .items}}{{.metadata.name}}{{"\n"}}{{end}}' | grep $NAMESPACE)

  # IMPORTANT: We copy the local exportSite.sh, this way we can make changes to it and test it directly ;)
  kubectl cp  ./app/ContentDump/exportSite.sh "$PODNAME:/app/ContentDump/exportSite.sh"
  kubectl -n $NAMESPACE exec $PODNAME -- ./ContentDump/exportSite.sh

  echo "Downloading database dump ..."
  kubectl -n $NAMESPACE cp $PODNAME:/app/ContentDump/Database.sql.gz ./app/ContentDump/Database.sql.gz
  echo "Downloading resource dump ..."
  kubectl -n $NAMESPACE cp $PODNAME:/app/ContentDump/Resources.tar.gz ./app/ContentDump/Resources.tar.gz

	_log_green "Prod content dump finished"
	_log_green "You can now run 'dev site-import' to import the dump"
}

# Import site from SQL dump and zipped resources
function site-import {
	# the import will be started inside the container if no site is found, which is the case
	# after calling down.
	_echo_yellow "IMPORTANT: Containers and data will be removed. Then the container will be restarted."
	_echo_yellow "The entrypoint.sh will check if a site can be found. If not, the SQL dump will be imported."
	down
	start
}

######################### Testing #########################

function start-e2e-testrunner {
  pushd ./e2e-testrunner
  nvm use
  npm install
  node index.js
  popd
}

function run-e2e-tests {
	docker compose exec maria-db "/createTestingDB.sh"
	docker compose exec neos bash -c ". /etc/bash.vips-arm64-hotfix.sh; FLOW_CONTEXT=Development/Docker/Behat ./flow doctrine:migrate"
	docker compose exec neos bash -c ". /etc/bash.vips-arm64-hotfix.sh; FLOW_CONTEXT=Development/Docker/Behat ./flow user:create --roles Administrator admin password LocalDev Admin"

	echo "TODO: check if testrunner is running and give hint"
	docker compose exec neos bin/behat -c Packages/Sites/MyVendor.AwesomeNeosProject/Tests/Behavior/behat.yml.dist -vvv
	echo
	echo "You can now run 'dev open-styleguide'"
}

function run-unit-tests {
	docker compose exec neos bash -c "./bin/phpunit -c Build/BuildEssentials/PhpUnit/UnitTests.xml Packages/Sites/MyVendor.AwesomeNeosProject/Tests/Unit"
}

function run-functional-tests {
	docker compose exec neos bash -c "./bin/phpunit -c Build/BuildEssentials/PhpUnit/FunctionalTests.xml Packages/Sites/MyVendor.AwesomeNeosProject/Tests/Functional"
}

_echo_green "------------- Running task $@ -------------"

# THIS NEEDS TO BE LAST!!!
# this will run your tasks
"$@"
