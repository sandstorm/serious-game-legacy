# Sandstorm Laravel on Docker Kickstart

[//]: # (KICKSTART_INFO_SECTION__START)

Features of Laravel kickstart:

- Docker Compose setup very similar to our Neos setup
- helpful packages configured

Well working packages:

- bezhansalleh/filament-language-switch
- bezhansalleh/filament-shield
- /jeffgreco13/filament-breezy
- https://laravel.com/docs/11.x/telescope#local-only-installation

Still open topics:

- settings? filament/spatie-laravel-settings-plugin only meh
- larastan etc



This packages helps you to quickly set up a Laravel/Filament Project. Besides a basic Laravel and Filament setup we included lots of best practices in the application, so you'll be up and running in no time.

This project is FORKED from neos-on-kickstart, so INFRASTRUCTURE changes can be
downmerged from there.

You need composer on your machine to use kickstarter. If you don't have composer installed, you can install it via homebrew:
```
brew install composer
```

To start, simply fork this repo and start coding :)

**We suggest that you keep the history of the kickstarter in your project, as this will
make it easier to pull upcoming changes from the kickstarter to your project.**

[//]: # (KICKSTART_INFO_SECTION__END)

<!-- TOC -->
* [Sandstorm Neos on Docker Kickstart](#sandstorm-neos-on-docker-kickstart)
  * [Requirements](#requirements)
  * [Features](#features)
  * [Initial Setup (required once)](#initial-setup-required-once)
    * [Install Dependencies](#install-dependencies)
    * [Setting up IntelliJ](#setting-up-intellij)
  * [Local Development](#local-development)
    * [Local Development of Sandstorm Packages](#local-development-of-sandstorm-packages)
  * [Testing](#testing)
  * [Staging](#staging)
    * [htaccess protection for staging](#htaccess-protection-for-staging)
  * [Production Setup](#production-setup)
    * [Important URLs](#important-urls)
    * [Production Cookbook / Tips and Tricks](#production-cookbook--tips-and-tricks)
      * [my Laravel container does not start, how do I debug?](#my-laravel-container-does-not-start-how-do-i-debug)
      * [Connecting to the production database](#connecting-to-the-production-database)
  * [Backlog](#backlog)
* [Sandstorm Laravel / Filament Best Practices](#sandstorm-laravel--filament-best-practices)
  * [Filament fully set up](#filament-fully-set-up)
  * [Prepared for Multiple Filament Panels](#prepared-for-multiple-filament-panels)
  * [ULIDs as IDs](#ulids-as-ids)
  * [Laravel Strict Mode](#laravel-strict-mode)
  * [Installed Packages for Laravel / Filament](#installed-packages-for-laravel--filament)
    * [archilex/filament-filter-sets (Advanced Tables - Commercial Plugin)](#archilexfilament-filter-sets-advanced-tables---commercial-plugin)
    * [awcodes/filament-tiptap-editor (custom and extensible Rich Text Editor)](#awcodesfilament-tiptap-editor-custom-and-extensible-rich-text-editor)
    * [Record Finder Pro (more advanced Record Selector)](#record-finder-pro-more-advanced-record-selector)
    * [dutchcodingcompany/filament-developer-logins (development logins)](#dutchcodingcompanyfilament-developer-logins-development-logins)
    * [spatie/laravel-ignition (beautiful error pages in dev)](#spatielaravel-ignition-beautiful-error-pages-in-dev)
    * [barryvdh/laravel-ide-helper (IDE autocompletion)](#barryvdhlaravel-ide-helper-ide-autocompletion)
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

- Plugins:
  - [Laravel IDEA](https://plugins.jetbrains.com/plugin/13441-laravel-idea)
    - paid plugin, but REALLY good
  - [Laravel Query](https://plugins.jetbrains.com/plugin/16309-laravel-query)
  - [PHP](https://plugins.jetbrains.com/plugin/6610-php)
  - [PHP Annotations](https://plugins.jetbrains.com/plugin/7320-php-annotations)
  - [PHP Toolbox](https://plugins.jetbrains.com/plugin/8133-php-toolbox)
  - [Pest](https://plugins.jetbrains.com/plugin/14636-pest)
  - [Docker](https://plugins.jetbrains.com/plugin/7724-docker)
  - [Symlink Excluder](https://plugins.jetbrains.com/plugin/16110-symlink-excluder)
    - Prevents symlinked folders to be indexed by the IDE so that you don't have to exclude packages you're developing manually
- All plugins are defined as required in the IDE config. You'll get a popup prompting you to install the plugins if you don't already have them.

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

TODO: Tests in CI

## PHPStan Static Code Analysis


```bash
# NOTE: we have a TEAM LICENSE for all of sandstorm - so feel free to use --watch // --pro web UI (all configured for it).
dev phpstan --pro
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

## Staging

run `dev open-staging` to open the staging url in the browser.

Before you can use staging, you need to add a new namespace in rancher. 
The namespace should be named like the project, e.g. `myvendor-awesomeneosproject-staging`.

### htaccess protection for staging

Read the [htaccess documentation](https://gitlab.sandstorm.de/infrastructure/k8s/-/blob/main/operators/one-container-one-port/helm-charts/one-container-one-port/values.yaml#L220) for more information.
Basically, you need to add a secret to the namespace in rancher.

## Production Setup

TODO 

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

## Filament fully set up

- Filament with custom theme (needed for easy plugin integration)
- Database Notifications set up
- basic print.css style included

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

## 

## THROW for storages

TODO WRITE ME AND FIX ME

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

beautiful error pages with lots of context and information in development mode

### barryvdh/laravel-ide-helper (IDE autocompletion)

You need to use `dev setup-laravel-autocomplete` when Laravel is running. This
generates the following files containing autocompletes for lots of Laravel
Methods:

/app/.phpstorm.meta.php
/app/_ide_helper.php
/app/_ide_helper_models.php

