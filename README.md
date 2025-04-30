#  Serious Game Legacy + Moonshot von Uni Konstanz

<!-- TOC -->
* [Serious Game Legacy + Moonshot von Uni Konstanz](#serious-game-legacy--moonshot-von-uni-konstanz)
  * [Requirements](#requirements)
  * [Features](#features)
  * [Initial Setup (required once)](#initial-setup-required-once)
    * [Install Dependencies](#install-dependencies)
    * [Setting up IntelliJ](#setting-up-intellij)
  * [Local Development](#local-development)
    * [Local Development of Sandstorm Packages](#local-development-of-sandstorm-packages)
  * [Testing](#testing)
  * [PHPStan Static Code Analysis](#phpstan-static-code-analysis)
  * [Pint Code Style Fixer](#pint-code-style-fixer)
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
  * [Backlog](#backlog)
* [Sandstorm Laravel / Filament Best Practices](#sandstorm-laravel--filament-best-practices)
  * [Suggested Architecture of Laravel Applications: Ports & Adapters](#suggested-architecture-of-laravel-applications-ports--adapters)
  * [Only Use Dependency Injection, no stateful helpers or Laravel Facades](#only-use-dependency-injection-no-stateful-helpers-or-laravel-facades)
  * [Where to use Laravel Eloquent (ORM), and where not? TODO](#where-to-use-laravel-eloquent-orm-and-where-not-todo)
  * [Filament fully set up](#filament-fully-set-up)
  * [Lightweight Permissions with AppAuthorizer](#lightweight-permissions-with-appauthorizer)
  * [Prepared for Multiple Filament Panels](#prepared-for-multiple-filament-panels)
  * [ULIDs as IDs](#ulids-as-ids)
  * [Laravel Strict Mode](#laravel-strict-mode)
  * [throw exceptions on error for file system storage](#throw-exceptions-on-error-for-file-system-storage)
  * [Installed Packages for Laravel / Filament](#installed-packages-for-laravel--filament)
    * [archilex/filament-filter-sets (Advanced Tables - Commercial Plugin)](#archilexfilament-filter-sets-advanced-tables---commercial-plugin)
    * [awcodes/filament-tiptap-editor (custom and extensible Rich Text Editor)](#awcodesfilament-tiptap-editor-custom-and-extensible-rich-text-editor)
    * [Record Finder Pro (more advanced Record Selector)](#record-finder-pro-more-advanced-record-selector)
    * [dutchcodingcompany/filament-developer-logins (development logins)](#dutchcodingcompanyfilament-developer-logins-development-logins)
    * [spatie/laravel-ignition (beautiful error pages in dev)](#spatielaravel-ignition-beautiful-error-pages-in-dev)
    * [barryvdh/laravel-ide-helper (IDE autocompletion)](#barryvdhlaravel-ide-helper-ide-autocompletion)
    * [jeffgreco13/filament-breezy (Two Factor Authentication & My Profile Page)](#jeffgreco13filament-breezy-two-factor-authentication--my-profile-page)
    * [Further Package Suggestions](#further-package-suggestions)
<!-- TOC -->

## Requirements

- docker for mac

## Features

- Neos 8.3
- PHP 8.3
- MariaDB 10.11
- Supercronic
- Bash-Highlighting (dev, staging, production)
- Gitlab-CI Pipeline Config
  - Kubernetes Deployment
  - E2E Tests
  - Functional Tests
  - Unit Tests
- Testsetup
  - Functional and Unit
  - Behavioural Tests
- Swiftmailer + Mailhog

## Initial Setup (required once)

### Install Dependencies

- run `./dev.sh setup` to install the Dev Script Runner and other dependencies. You can now use `dev <some-taks>` from anywhere
  inside the project.

### Setting up IntelliJ

All plugins are defined as required in the IDE config.
You'll get a popup prompting you to install the plugins if you don't already have them.

- Plugins:
  - [Laravel IDEA](https://plugins.jetbrains.com/plugin/13441-laravel-idea)
    - paid plugin, but REALLY good
  - Blade Support (for template files)
  - [Laravel Query](https://plugins.jetbrains.com/plugin/16309-laravel-query)
  - [PHP](https://plugins.jetbrains.com/plugin/6610-php)
  - [PHP Annotations](https://plugins.jetbrains.com/plugin/7320-php-annotations)
  - [PHP Toolbox](https://plugins.jetbrains.com/plugin/8133-php-toolbox)
  - [Pest](https://plugins.jetbrains.com/plugin/14636-pest)
  - [Docker](https://plugins.jetbrains.com/plugin/7724-docker)
  - [Symlink Excluder](https://plugins.jetbrains.com/plugin/16110-symlink-excluder)
    - Prevents symlinked folders to be indexed by the IDE so that you don't have to exclude packages you're developing manually

## Local Development

- run `dev start` to start all needed container of the project (see `docker-compose.yml` for details)
- run `dev ps` to see if all containers start up correctly.
- run `dev logs` to see the logs of all containers.
- run `dev` to see all available development tasks
- run `dev <sometaks> --help` to get detailed help for a task
- run `dev open-site` you can login to the [neos backend](http://localhost:8081/neos) with the credentials `admin` and `password`
- Have fun ;)

### Local Development of Sandstorm Packages

- run `composer config --global 'preferred-install.sandstorm/*' source` on your local machine to use the local packages for development
- run `composer install` in the `app` folder to install the local packages or start the container, the Packages folder is mounted into the container
- now you can develop the packages in the `Application/Packages` folder and the changes will be reflected in the docker container
- the sandstorm packages should check out the branch specified in the `composer.json` of the kickstarter

## Testing

- run `dev run-unit-tests` to run all unit tests
- run `dev run-functional-tests` to run all functional tests
- run `dev start-e2e-testrunner` and in new console `dev run-e2e-tests` to run all e2e tests or use `dev run-e2e-tests --tags=<yourTestTag>` to run a single test, e.g a test which is annotated with `@EventList` can be run with `dev run-e2e-tests --tags=EventList`

We suggest to use [Pest](https://pestphp.com/) as testing framework, which builds upon
PHPUnit and looks as if it provides quite some nice benefits on top,
especially [Architectural Testing](https://pestphp.com/docs/arch-testing) and
[Snapshot Testing](https://pestphp.com/docs/snapshot-testing) is something we
always looked for in PHP Test Frameworks.
Some features like
[Stress Testing](https://pestphp.com/docs/stress-testing),
[Mutation Testing](https://pestphp.com/docs/mutation-testing),
[Team Management](https://pestphp.com/docs/team-management) might be useful as
well.

> NOTE: we might be able to archive architectural tests as well with PHPStan - we
> still have to see which project does the job better.

```bash
# run the tests
dev pest

# run the tests - watching if something changes
dev pest --watch
```

**Tests in IntelliJ IDEA**

You can run Pest tests directly in IntelliJ IDEA. To make this work, the following was configured:

- Settings -> Languages&Frameworks -> PHP
  - CLI Interpreters -> add new one **From Docker** - name it 'php-in-docker'
    - pick *docker compose* and the right service `laravel`.
    - after saving, underneath `Lifecycle` of the newly created record, say `docker compose exec`
  - Choose the new `php-in-docker` as default CLI interpreter

This allowed running full Pest files, but not single tests due to [this bug](https://youtrack.jetbrains.com/issue/WI-79139). A workaround is described here, which is also applied to the project:

- Run -> Edit Configurations -> (at the very bottom left) Edit Configuration Templates ... -> Pest
- select `<your...project...dir>/app` as custom working directory
- As extra env, add `DB_DATABASE=laravel_testing` so that the testing DB is used.
- on the top right, choose *Store as project file*

TODO: Tests in CI

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


TODO: PHPStan in CI

## Pint Code Style Fixer

We include [Laravel Pint](https://laravel.com/docs/11.x/pint) which "is an opinionated
PHP code style fixer for minimalists", which formats our code according to PSR-12.

```bash
# dry run - which files would change?
dev pint --test

# pretty-print the files
dev pint
```

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

### Production Cookbook / Tips and Tricks

#### my Laravel container does not start, how do I debug?

**Symptom:** There is an error in the Entrypoint Laravel container during `docker compose up -d`.

You can start the container without executing the entrypoint with the following line:

```bash
sudo su - deploy
cd [project-name]

docker compose run --entrypoint /bin/bash laravel
```

#### Connecting to the production database

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

[//]: # (KICKSTART_INFO_SECTION__START)

## Backlog

- Examples for rights in Neos -> separate Distribution Package
- Custom Backend Module Beispiel in extra Distribution Package
- Frontendlogin in extra Distribution Package
- DataPrivacy -> maybe change config of Neos
- check caching config -> nginx e.g. images
- Distribution Package with search
- Check examples for accessibility issues

[//]: # (KICKSTART_INFO_SECTION__END)


# Sandstorm Laravel / Filament Best Practices

## Suggested Architecture of Laravel Applications: Ports & Adapters

**Problems with the standard "Framework" approach**

A big problem with Laravel applications is that they are essentially **guiding you
into the framework** - so that means all tutorials, guides, ... show you "the laravel
way" and how to embrace this ecosystem.

While, on the one side, this leads to pretty standardized applications in terms of
where every component is located, it has the big problem that the **core domain
of the software is spread out** across many controllers, services, event handlers,
and many other framework constructs. We've teached the same with Flow applications,
and in my current (Sebastian, 2025) understanding this is one of the biggest mistakes
we made.


**Goals**

We want to focus on the business logic, and want to make code easily testable. Ports
& Adapters **guide the code towards easy testability**.


**When not to use Ports&Adapters**

- a pure CRUD application with no core business logic (i.e. only "Datenpflege")


**How to learn about Ports & Adapters**

- a book: https://gitlab.sandstorm.de/sandstorm/books/-/blob/main/Tech/00__CONCEPTS_MUST_READ/2025_Hexagonal_Architecture_Explained.epub?ref_type=heads
- a video for hexagonal architecture: https://www.youtube.com/watch?v=UwQSfyYrSrg
  - core idea around 09:40
- Talk to Sebastian, who learned this from Bastian Waidelich :)

**Our Naming Convention**

The Core Domain should reside inside `Domain\` namespace (OUTSIDE of the laravel
`App\` namespace) in a SEPARATE Library package. Our suggested structure is:

```
src/ folder (Namespace Domain\)
├── CoreDomainX/             # A bounded context of your domain
│   ├── DrivingPorts/        # "Input" ports - how external code calls your domain
│   │   └── ForXY.php        # Interface defining allowed operations
│   ├── DrivenPorts/         # "Output" ports - how your domain calls external services
│   │   ├── ForPersistence/  # f.e. Database operations, ...
│   │   └── ForLogging/      # f.e. Logging operations, ...
│   ├── Dto/                 # Data Transfer Objects (Immutable)
│   └── CoreDomainXApp.php   # Main implementation of business logic
```

For DTOs, we recommend to use https://github.com/bwaidelich/types.

The Core Domain should NEVER depend on any Laravel class.

The Adapters should reside in `\App\Adapters` in the Laravel application.

**Ports&Adapters Summary**

❌ Don't Do This

- Accessing Laravel facades or helpers in domain code
- Putting business logic in adapters
- Creating circular dependencies between ports
- Using framework-specific types in DTOs

✅ Do This Instead

- Inject all dependencies through ports
- Keep adapters thin and focused on translation
- Design ports around business concepts, not technical ones
- Use framework-agnostic value objects in DTOs

## Only Use Dependency Injection, no stateful helpers or Laravel Facades

Methods like `env()` or `app()` totally break encapsulation in Laravel; please DO NOT USE them but instead
inject the underlying object via Dependency Injection. This way, we can more easily analyze the code base
because we have less "magic connections" between classes which are invisible to the outside.

in `disallowed-calls.neon`, we test for this; so phpstan fails in case this is violated.

## Where to use Laravel Eloquent (ORM), and where not? TODO

❌ Don't use Laravel Eloquent (ORM) for:

TODO

✅ Use Laravel Eloquent (ORM) for:

TODO

## Filament fully set up

- Filament with custom theme (needed for easy plugin integration)
- Database Notifications set up
- basic print.css style included
- For Asynchronous work: Laravel Queues (with Redis) set up, with a running queue worker
  - in case your application is queue heavy, install Laravel Horizon for monitoring
- Laravel Scheduler set up, based on Supercronic /crontab
- Laravel Caches (with Redis) set up
- TODO: Laravel Broadcast?

## Lightweight Permissions with AppAuthorizer

NOTE: we tried permission systems based on spatie/laravel-permission (using bezhansalleh/filament-shield),
and based on casbin.org.
For us, they were too complex on one side and too restrictive on the other side (both at the
same time). That's why we are now rolling our own (very simple) permission system in
`App\Authorization\AppAuthorizer`, which helps us centralize all permission logic at a single
point.

See `App\Authorization\AppAuthorizer` for further details.

## Prepared for Multiple Filament Panels

Contrary to the default Filament kickstart, we use `App\Filament\Admin` as namespace
for Resources, Pages, and Widgets. This way, we can easily create a new Panel which
can e.g. use the `App\Filament\Frontend` namespace.

## ULIDs as IDs

We strongly suggest to use ULIDs as IDs - they are shorter than UUIDs and thus easier
to work with visually; but still globally unique. NOTE - they are incremented roughly,
so you should NOT DEPEND on the RANDOMNESS of ULIDs (same as with UUIDs, which also
have an incremental mode).

## Laravel Strict Mode

see https://laravel-news.com/shouldbestrict - catches quite some errors. NOTE we only
enable this in dev mode, as we want our dev mode to be more restrictive, and our prod
mode more forgiving.

## throw exceptions on error for file system storage

By default, file storage errors do not throw exceptions but simply return `false`. This has the problem that
we manually need to test for successful uploads; and we don't get good error messages what is going wrong.

In our applications, we found it much better to throw on storage errors. This works easily with setting `'throw' => true`
inside `config/filesystems.php` (this is already configured here).

## Installed Packages for Laravel / Filament

### archilex/filament-filter-sets (Advanced Tables - Commercial Plugin)

Main features: greatly extended Listing Tables by user customizable views, reorderable and hideable columns, custom filters, ...

This is a MUST HAVE for any Laravel / Filament application. We have it licensed for all
Sandstorm projects. It is well documented at https://filamentphp.com/plugins/kenneth-sese-advanced-tables .

The package is fully installed in the boilerplate. You only need to add the trait
`use AdvancedTables;` for every `ListXY` class (extending from `ListRecords`).

To update, use `dev composer-update-filament-advanced-tables`.

### awcodes/filament-tiptap-editor (custom and extensible Rich Text Editor)

based on ProseMirror, which is a really clean and extensible Rich Text Editor.

- TODO: custom extension

###  Record Finder Pro (more advanced Record Selector)

Replace your `Select`'s with beautiful Record Finders to make searching easier.
Works everywhere out-of-the-box & integrates with resources.

This is a SUGGESTION for every NEW Laravel / Filament application.
We have it licensed for all  Sandstorm projects. It is well documented at
https://filamentphp.com/plugins/ralphjsmit-record-finder-pro .

The package is fully installed in the boilerplate.

To update, use `dev composer-update-filament-record-finder-pro`.

### dutchcodingcompany/filament-developer-logins (development logins)

one-click logins for all `...@example.com` logins in Local Development.

### spatie/laravel-ignition (beautiful error pages in dev)

beautiful error pages with lots of context and information in development mode.

It is configured with path mapping, so you can DIRECTLY click on a stack trace line and
IntelliJ opens the appropriate file.

### barryvdh/laravel-ide-helper (IDE autocompletion)

You need to use `dev setup-laravel-autocomplete` when Laravel is running. This
generates the following files containing autocompletes for lots of Laravel
Methods:

/app/.phpstorm.meta.php
/app/_ide_helper.php
/app/_ide_helper_models.php

### jeffgreco13/filament-breezy (Two Factor Authentication & My Profile Page)

Includes a User Profile Page, so the users can:

- Change Personal Information (Name, Email)
- Change Password
- Enable OTP based Two Factor Authentication
- (optionally) manage API tokens via laravel/sanctum

### Further Package Suggestions

- bezhansalleh/filament-language-switch
  - in case you need a language switcher
