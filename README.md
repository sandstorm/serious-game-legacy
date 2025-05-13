#  Serious Game Legacy + Moonshot von Uni Konstanz

[![PHP Tests and Analysis](https://github.com/sandstorm/serious-game-legacy/actions/workflows/php-tests.yml/badge.svg)](https://github.com/sandstorm/serious-game-legacy/actions/workflows/php-tests.yml) [![Package App Staging](https://github.com/sandstorm/serious-game-legacy/actions/workflows/package-app-staging.yml/badge.svg)](https://github.com/sandstorm/serious-game-legacy/actions/workflows/package-app-staging.yml)


Entwickelt als Open Source von Sandstorm Media GmbH.

[README](README.md) [CODE_STYLE](CODE_STYLE.md) [ARCHITECTURE](ARCHITECTURE.md)

<!-- TOC -->
* [Serious Game Legacy + Moonshot von Uni Konstanz](#serious-game-legacy--moonshot-von-uni-konstanz)
* [Initial Setup (required once)](#initial-setup-required-once)
  * [Requirements](#requirements)
  * [Install Dependencies](#install-dependencies)
  * [Setting up IntelliJ](#setting-up-intellij)
  * [Generate Helper Code](#generate-helper-code)
  * [Starting the Development Environment](#starting-the-development-environment)
* [Local Development](#local-development)
  * [Testing](#testing)
    * [Testing Tricks](#testing-tricks)
  * [PHPStan Static Code Analysis](#phpstan-static-code-analysis)
  * [Pint Code Style Fixer](#pint-code-style-fixer)
* [Staging and Production Setup](#staging-and-production-setup)
  * [Observability & Logging](#observability--logging)
    * [Health Check Endpoint](#health-check-endpoint)
    * [Laravel Pulse - Server Metrics](#laravel-pulse---server-metrics)
    * [Laravel Horizon - Queue Metrics](#laravel-horizon---queue-metrics)
    * [Laravel Telescope - Local Development - Deep Insights](#laravel-telescope---local-development---deep-insights)
    * [Structured Logging](#structured-logging)
  * [Staging](#staging)
    * [htaccess protection for staging](#htaccess-protection-for-staging)
    * [Initial Staging Deployment: set Encryption Key](#initial-staging-deployment-set-encryption-key)
  * [Production Setup](#production-setup)
    * [Error Page During Deployment](#error-page-during-deployment)
    * [Important URLs](#important-urls)
  * [Production Cookbook / Tips and Tricks](#production-cookbook--tips-and-tricks)
    * [my Laravel container does not start, how do I debug?](#my-laravel-container-does-not-start-how-do-i-debug)
    * [Connecting to the production database](#connecting-to-the-production-database)
* [Sandstorm Laravel / Filament Best Practices](#sandstorm-laravel--filament-best-practices)
<!-- TOC -->

# Initial Setup (required once)

## Requirements

- docker for mac
- Node.js / NPM

## Install Dependencies

- run `./dev.sh setup` to install the Dev Script Runner and other dependencies. You can now use `dev <some-taks>` from anywhere
  inside the project.

## Setting up IntelliJ

**All plugins are defined as required in the IDE config.**
You'll get a popup prompting you to install the plugins if you don't already have them.

- Plugins:
  - [Laravel IDEA](https://plugins.jetbrains.com/plugin/13441-laravel-idea)
    - paid plugin, but REALLY good
  - Blade Support (for template files)
  - [PHP](https://plugins.jetbrains.com/plugin/6610-php)
  - [PHP Annotations](https://plugins.jetbrains.com/plugin/7320-php-annotations)
  - [PHP Toolbox](https://plugins.jetbrains.com/plugin/8133-php-toolbox)
  - [Pest](https://plugins.jetbrains.com/plugin/14636-pest)
  - [Docker](https://plugins.jetbrains.com/plugin/7724-docker)
  - [Symlink Excluder](https://plugins.jetbrains.com/plugin/16110-symlink-excluder)
    - Prevents symlinked folders to be indexed by the IDE so that you don't have to exclude packages you're developing manually

## Generate Helper Code

When using IntelliJ/PHPStorm, install the (paid) Laravel IDEA Plugin.
Then, in the main menu (top bar), go to **Laravel -> Generate Helper Code.**

This will generate (invisible) code which helps with autocompletion on Model properties etc.

## Starting the Development Environment

```bash
git submodule init
git submodule update

dev setup

# start everything
dev start

# start JS/CSS watcher
# may need to run `nvm use` inside /app 
dev watch-js

# now, access the frontend at http://127.0.0.1:8090
# now, access the filament backend at http://127.0.0.1:8090/admin
```

# Local Development

- run `dev start` to start all needed container of the project (see `docker-compose.yml` for details)
- run `dev ps` to see if all containers start up correctly.
- run `dev logs` to see the logs of all containers.
- run `dev` to see all available development tasks
- run `dev <sometaks> --help` to get detailed help for a task
- run `dev open-site`
  - Frontend: http://127.0.0.1:8090/
  - Admin UI: http://127.0.0.1:8090/admin 
- Have fun ;)

## Testing

Run `dev pest` to run all unit tests.

We use [Pest](https://pestphp.com/) as testing framework, which builds upon
PHPUnit and looks as if it provides quite some nice benefits on top,
especially [Architectural Testing](https://pestphp.com/docs/arch-testing) and [Snapshot Testing](https://pestphp.com/docs/snapshot-testing) is something we
always looked for in PHP Test Frameworks.
Some features like [Stress Testing](https://pestphp.com/docs/stress-testing), [Mutation Testing](https://pestphp.com/docs/mutation-testing), [Team Management](https://pestphp.com/docs/team-management)
might be useful as well.

```bash
# run the tests
dev pest

# run the tests - watching if something changes
dev pest --watch
```

**Tests in IntelliJ IDEA**

You can run Pest tests directly in IntelliJ IDEA. This is configured by default.

> **Setting up Pest tests in PHPStorm/IntelliJ:**
> 
> - Settings -> Languages&Frameworks -> PHP
>   - CLI Interpreters -> add new one **From Docker** - name it 'php-in-docker'
>     - pick *docker compose* and the right service `laravel`.
>     - after saving, underneath `Lifecycle` of the newly created record, say `docker compose exec`
>   - Choose the new `php-in-docker` as default CLI interpreter
> 
> This allowed running full Pest files, but not single tests due to [this bug](https://youtrack.jetbrains.com/issue/WI-79139). A workaround is described here, which is also applied to the project:
> 
> - Run -> Edit Configurations -> (at the very bottom left) Edit Configuration Templates ... -> Pest
> - select `<your...project...dir>/app` as custom working directory
> - As extra env, add `DB_DATABASE=laravel_testing` so that the testing DB is used.
> - on the top right, choose *Store as project file*

Now, to run tests from your IDE, make sure the server is started via `dev start`; then press "play" on the gutter of a
PEST Test.

**Tests are executed in CI.**

### Testing Tricks

- If you need a database initialized in your tests (`laravel_testing` is used in our case), add

  ```php
  uses(RefreshDatabase::class);
  ```

## PHPStan Static Code Analysis

PHPStan is a very powerful static code analyzer.

USAGE:

```bash
# NOTE: we have a TEAM LICENSE for all of sandstorm - so feel free to use --watch // --pro web UI (all configured for it).
dev phpstan --pro
```

We use PHPStan with a few addons:
- strict PHPStan rules via phpstan/phpstan-strict-rules
- alert on calling `@deprecated` methods via phpstan/phpstan-deprecation-rules
- larastan (for laravel specific rules)
- spaze/phpstan-disallowed-calls with possible custom rules: https://github.com/spaze/phpstan-disallowed-calls/blob/main/docs/custom-rules.md
  - this enables us to disallow Laravel Facades and helpers, and enforce dependency injection. see `disallowed-calls.neon` for docs.
- TODOs which turn into errors via staabm/phpstan-todo-by - see [examples](https://github.com/staabm/phpstan-todo-by?tab=readme-ov-file#examples) for demo:
  ```php
  // TODO: 2023-12-14 This comment turns into a PHPStan error as of 14th december 2023
  function doFoo() { /* ... */ }
  
  // TODO https://github.com/staabm/phpstan-todo-by/issues/91 fix me when this GitHub issue is closed
  class FooClass {}
  
  // TODO: <1.0.0 This has to be in the first major release of this repo
  function doBar() { /* ... */ }
  
  // FIXME: phpunit/phpunit:5.3 This has to be fixed when updating phpunit to 5.3.x or higher
  function doFooBar() { /* ... */ }
  
  // XXX: php:8 drop this polyfill when php 8.x is required
  
  // TODO: APP-2137 A comment which errors when the issue tracker ticket gets resolved
  function doBaz() { /* ... */ }
  ```


**PHPStan is executed in CI.**

## Pint Code Style Fixer

We include [Laravel Pint](https://laravel.com/docs/11.x/pint) which "is an opinionated
PHP code style fixer for minimalists", which formats our code according to PSR-12.

```bash
# dry run - which files would change?
dev pint --test

# pretty-print the files
dev pint
```

We do NOT run Pint in CI; as this seems overly nitpicky.

# Staging and Production Setup

## Observability & Logging

We have the following observability enabled:

### Health Check Endpoint

There is a `/up` health check endpoint configured, which response with status 200 if everything is basically up and running.

In this health check, we also check for database and redis health (implemented in `App\Listeners\BasicHealthChecks`).

### Laravel Pulse - Server Metrics

At `/pulse`, an endpoint is configured which renders application metrics, including slow queries, caches, slow requests, ...

In `AppAuthorizer`, the `viewPulse` permission gate is configured such that only superadmins can reach
this gate. This can be adjusted based on the application roles.

### Laravel Horizon - Queue Metrics

We have laravel horizon available and configured for monitoring queues at `/horizon`.

In `AppAuthorizer`, the `viewHorizon` permission gate is configured such that only superadmins can reach
this gate. This can be adjusted based on the application roles.

### Laravel Telescope - Local Development - Deep Insights

For local dev, at `/telescope`, an endpoint is configured for deep insights. There you can find details about running
commands, web requests, database queries, views rendered, and lots of deep-insight information.

### Structured Logging

By default, we create JSON based logs and plain text logs.
In local dev, they can be also seen in `/telescope`.

For every request, we assign a Request ID which is stored as `request-id` on the log message, and returned
via a `X-Request-Id` HTTP header to the client. This is implemented in the middleware `AssignRequestId`.

## Staging

run `dev open-staging` to open the staging url in the browser.

Before you can use staging, you need to add a new namespace in rancher. 
The namespace should be named like the project, e.g. `myvendor-awesomeneosproject-staging`.

### htaccess protection for staging

Read the [htaccess documentation](https://gitlab.sandstorm.de/infrastructure/k8s/-/blob/main/operators/one-container-one-port/helm-charts/one-container-one-port/values.yaml#L220) for more information.
Basically, you need to add a secret to the namespace in rancher.

### Initial Staging Deployment: set Encryption Key

You need to generate an encryption key locally:

```bash
ENCRYPTION_KEY=$(docker compose exec laravel /app/artisan key:generate --show)

# make sure you are in correct namespace
sku ns helvetikit-prod

kubectl create secret generic laravel-encryption-key --from-literal=APP_KEY=$ENCRYPTION_KEY
```

## Production Setup

TODO 

### Error Page During Deployment

During deployment, i.e. when the ingress-caddy-proxy is up and running, but the application is not responding,
the `application-unavailable.html` page is shown. Its contents can be adjusted by modifying
`app/resources/views/mail/application-unavailable.blade.php`, and then running:

```bash
# updates the application-unavailable.html page.
./dev.sh render-application-unavailable

# now, COMMIT AND DEPLOY!
```

The resulting page must be committed and deployed. This error page shows for 502 only.

### Important URLs

TODO

## Production Cookbook / Tips and Tricks

### my Laravel container does not start, how do I debug?

**Symptom:** There is an error in the Entrypoint Laravel container during `docker compose up -d`.

You can start the container without executing the entrypoint with the following line:

```bash
sudo su - deploy
cd [project-name]

docker compose run --entrypoint /bin/bash laravel
```

### Connecting to the production database

When connecting to the system via SSH, use `-L` for a local port forward like this:

```bash
ssh -p29418 -L 23306:127.0.0.1:13306 [hostname]
```

Then, you can use the built-in DB browser of IntelliJ to connect:

- User `laravel`
- Password: see bitwarden `TODO`
- Host: 127.0.0.1
- **Port 23306**
- Database: `laravel`

# Sandstorm Laravel / Filament Best Practices

**See [CODE_STYLE.md](CODE_STYLE.md) for Sandstorm Laravel/Filament Best Practices.**
