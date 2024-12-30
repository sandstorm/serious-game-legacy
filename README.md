# Sandstorm Neos on Docker Kickstart

[//]: # (KICKSTART_INFO_SECTION__START)

Features of Laravel kickstart:

- Docker Compose setup very similar to our Neos setup
- Filament with custom theme
- Prepare for multiple Filament panels (Admin area, Frontend area)

This packages helps you to quickly set up a Neos Project. Besides a basic Neos setup
we provided examples and configuration that helps us to quickly provide a kickstart.

You have to use git lfs for the ContentDump! Therefore, after pulling the current main,
you also have to run git lfs pull to get the database to working.

Run `brew install git-lfs`to install git lfs.
```
git lfs install
git lfs pull
```

You need composer on your machine to use kickstarter. If you don't have composer installed, you can install it via homebrew:
```
brew install composer
```
Run `./kickstart.sh` and follow the instructions.

[//]: # (KICKSTART_INFO_SECTION__END)

<!-- TOC -->
* [Sandstorm Neos on Docker Kickstart](#sandstorm-neos-on-docker-kickstart)
  * [Requirements](#requirements)
  * [Features](#features)
  * [Initial Setup (required once)](#initial-setup-required-once)
    * [Install Dependencies](#install-dependencies)
    * [Setting up IntelliJ](#setting-up-intellij)
  * [Local Development](#local-development)
  * [Testing](#testing)
    * [E2E Test](#e2e-test)
      * [Debug Failing Tests](#debug-failing-tests)
      * [Run Single BDD Feature Files / Scenarios](#run-single-bdd-feature-files--scenarios)
      * [Generating content (node) fixtures workflow](#generating-content-node-fixtures-workflow)
      * [raise curl-timeouts when using `And I pause for debugging`](#raise-curl-timeouts-when-using-and-i-pause-for-debugging)
    * [Accessibility Tests](#accessibility-tests)
      * [Testing without htaccess (e.g. production)](#testing-without-htaccess-eg-production)
      * [Testing with htaccess (e.g. staging)](#testing-with-htaccess-eg-staging)
      * [Results](#results)
      * [Hint](#hint)
  * [Staging](#staging)
    * [run site import on staging](#run-site-import-on-staging)
    * [htaccess protection for staging](#htaccess-protection-for-staging)
    * [quality dashboard for staging](#quality-dashboard-for-staging)
  * [Site Export / Site Import](#site-export--site-import)
    * [Site Export Prod](#site-export-prod)
  * [Kickstart repository nodetypes](#kickstart-repository-nodetypes)
  * [Add components from the library to your project](#add-components-from-the-library-to-your-project)
    * [Development](#development)
  * [Custom icon font with icomoon](#custom-icon-font-with-icomoon)
    * [Use custom icons in neos backend](#use-custom-icons-in-neos-backend)
  * [Maps](#maps)
  * [Menu](#menu)
  * [Image sizes](#image-sizes)
  * [Recommended Packages](#recommended-packages)
  * [Coding Guidelines](#coding-guidelines)
  * [Improving Kickstart Experience](#improving-kickstart-experience)
  * [Production Setup](#production-setup)
    * [Important URLs](#important-urls)
    * [Matomo](#matomo)
    * [Production Cookbook / Tips and Tricks](#production-cookbook--tips-and-tricks)
      * [my Neos container does not start, how do I debug?](#my-neos-container-does-not-start-how-do-i-debug)
      * [Connecting to the production database](#connecting-to-the-production-database)
  * [Backlog](#backlog)
<!-- TOC -->

## Requirements

- docker for mac
  - enable VirtioFS in docker host settings (experimental features)
  - alternatively, comment out the volume mount in the docker-compose.yml if you encounter bad local performance
- node -> to run Playwright Tests or for local development (without docker) of your sites JavaScript
- git lfs

## Features

- Neos 8.3
- PHP 8.3
- MariaDB 10.11
- Vips (instead of ImageMagick)
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
    - Playwright Integration
  - Playwright Testrunner
- Swiftmailer + Mailhog

## Initial Setup (required once)

### Install Dependencies

- make sure [nvm](https://github.com/nvm-sh/nvm#installing-and-updating) is installed on your system
- run `./dev.sh setup` to install the Dev Script Runner and other dependencies. You can now use `dev <some-taks>` from anywhere
  inside the project.

### Setting up IntelliJ

- Plugins:
  - [Neos Support](https://plugins.jetbrains.com/plugin/9362-neos-support)
  - [PHP](https://plugins.jetbrains.com/plugin/6610-php)
  - [PHP Annotations](https://plugins.jetbrains.com/plugin/7320-php-annotations)
  - [PHP Toolbox](https://plugins.jetbrains.com/plugin/8133-php-toolbox)
  - [Prettier](https://plugins.jetbrains.com/plugin/10456-prettier)
    - make sure Prettier is activated for the correct extensions
  - [Docker](https://plugins.jetbrains.com/plugin/7724-docker)
  - [Behat Support](https://plugins.jetbrains.com/plugin/7512-behat-support)
  - [Symlink Excluder](https://plugins.jetbrains.com/plugin/16110-symlink-excluder)
    - Prevents symlinked folders to be indexed by the IDE so that you don't have to exclude packages you're developing manually
- All plugins are defined as required in the IDE config. You'll get a popup prompting you to install the plugins if you don't already have them.
- Set the `rootFontSize` for the tailwind plugin:
  - Settings -> Languages & Framework -> Style Sheets -> Tailwind CSS
  - You can find your root font size in your tailwind.config.js
- check, if autocompletion works for NodeType and Configuration yaml-files
- check, if it is possible to jump to Fusion Prototypes via cmd + click

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

### E2E Test

#### Debug Failing Tests

1. see screenshots in `./e2e-testrunner`
2. reports can be viewed by installing playwright once globally (`npm install -g playwright`) and run the traces via

```
npx playwright show-trace e2e-testrunner/report_YOUR_FAILING_STEP.zip
```

#### Run Single BDD Feature Files / Scenarios

In TDD it can be more practical to run single feature files or scenarios. That has to be done via command line, since the IntelliJ
integration "play button" does not work for our docker setup (afaik).

How to run single feature files (see also README of Sandstorm.E2ETestTools):

```
docker compose exec neos bin/behat -c Packages/Sites/MyVendor.AwesomeNeosProject/Tests/Behavior/behat.yml.dist -vvv Packages/Sites/MyVendor.AwesomeNeosProject/Tests/Behavior/Features/Frontend/404-page.feature
```

#### Generating content (node) fixtures workflow

1. set up your test content that you want to export in the Neos backend (using the default local dev port `8080`)
2. write new commands for exporting specific pages
   in `\MyVendor\AwesomeNeosProject\Command\StepGeneratorCommandController` to export only specific nodes for your
   fixtures
3. Run and copy to clipboard with:

    ```
    docker compose exec -T neos ./flow stepgenerator:homepage | pbcopy
    docker compose exec -T neos ./flow stepgenerator:notfoundpage | pbcopy
    ```

4. paste into your feature files and run tests

#### raise curl-timeouts when using `And I pause for debugging`

```shell
dev enter-neos
vim Packages/Application/Sandstorm.E2ETestTools/Tests/Behavior/Bootstrap/PlaywrightConnector.php
```

on line 158 raise the `CURL_TIMEOUT` option to a very high value.

```shell
:158
```

### Accessibility Tests

We have CI tasks for staging `a11y_test_staging` and production `a11y_test_production` which will create us an a11y test report as an job artifact.

The test uses pa11y, so have a look at <https://github.com/pa11y/pa11y-ci> for possible configuration options.

#### Testing without htaccess (e.g. production)

If we don't have an htaccess in front of the page, we can use the `.pa11yci` file to define all urls of the page we want to test. Just rename `.pa11yci.sample` to `.pa11yci` and add the urls you want to be tested. We also can make screen captures if we want to (see `.pa11yci.sample`). When we use it this way, we don't have to use the `--config` flag like we do in the `a11y_test_staging` job because it will pick up the `.pa11yci` config automatically.

#### Testing with htaccess (e.g. staging)

If we have an htaccess in front of the page we can write the pa11y config like we would do with the `.pa11yci` file but store it in a gitlab variable. It's importent that the variable is of type `file` (Settings > CI/CD > Variables). For the kickstarter we use the variable `$A11Y_TEST`. If we want to add an url we want to test, we can just edit this variable. If you cannot see the Settings just ask another Sandstormee with access rights ;)

#### Results

In both cases htaccess or not the results will be stored as job artifacts and can be downloaded. The html-report can be found in the directory `pa11y-ci-report` and if we decided to get some screenshots, they will be stored in the directory `pa11y-ci-report-images`.

#### Hint

We exclude the following rules for the kickstarter because they are more or less color contrast related. In the kickstarter we focus on structure-related issues. Nevertheless should the color contrast be checked in an actual project.
- WCAG2AA.Principle1.Guideline1_4.1_4_3.G18 / WCAG2AA.Principle1.Guideline1_4.1_4_3.G18.Fail
- WCAG2AA.Principle1.Guideline1_4.1_4_3.G18.Alpha
- WCAG2AA.Principle1.Guideline1_4.1_4_3.G18.Abs

Excluded Elements:
- a[href='#open_cookie_punch_modal'] > Cookie Punch Package
- .maplibregl-ctrl-attrib-inner > Maps Package

Usage of components:
- accordions should be used in a section with an h2 to keep the headline hierachy otherwise we have to update the accordion headlines accordingly

## Staging

run `dev open-staging` to open the staging url in the browser.

Before you can use staging, you need to add a new namespace in rancher. 
The namespace should be named like the project, e.g. `myvendor-awesomeneosproject-staging`.

### run site import on staging

Connect to the staging container via sku:
- set the namespace to `sku ns myvendor-awesomeneosproject-staging`
- enter the container with `sku enter`
- run `./ContentDump/importSite.sh` to import the local content dump into staging

!!! If you cant login into the neos backend do this:
- connect to the staging db with `sku mysql sequelace` (or any other db tool)
- open the table `neos_contentrepository_domain_model_workspace`
- deleted all entries except the one with baseworkspace=null (the local admin one)

### htaccess protection for staging

Read the [htaccess documentation](https://gitlab.sandstorm.de/infrastructure/k8s/-/blob/main/operators/one-container-one-port/helm-charts/one-container-one-port/values.yaml#L220) for more information.
Basically, you need to add a secret to the namespace in rancher.

### quality dashboard for staging

Read the [quality dashboard documentation](https://gitlab.sandstorm.de/infrastructure/sandstorm-quality-ci for more information) for more information.
You need to set two env variables in your gitlab project:
- $LHCI_URL_PASSWORD_STAGING (the ht access password for the staging website)
- $LHCI_TOKEN_STAGING (see the quality dashboard documentation for more information on how to generate it)
Adjust the `quality_ci-lhci_staging` job in the `ci/staging.gitlab-ci.yml` to your needs.


## Site Export / Site Import

The default flow command to import and export the site content is not stable. This is why we dump the database and copy the resources.
The files and scripts for creating them can be found in `app/ContentDump`.

run `dev site-export` to export a site

run `dev site-import` to import a site

### Site Export Prod

For local development, if you want to use `site-export-prod` make sure to run the following commands locally beforehand:

```
sku context k3s2021
sku ns myvendor-awesomeneosproject-staging
```

Otherwise, you may get an error that the pods could not be found.
If you're not working at sandstorm update this command for your environment. :)

## Kickstart repository nodetypes

We found that to create repository nodetypes with a parent, several children and some kind of aggregation ("show me all
child nodes as teasers") you would have to do some repetitive tasks like creating the yaml files for all node types,
write the fusion aggregation logic, write some basic presentational code, consider caching, and so on. You can
use `dev generate-repository` to have all this work be done for you :)

The command will create the following files:

- `NodeTypes/Document/Document.Repository.yaml`
- `NodeTypes/Document/Document.Repository.Item.yaml`
- `NodeTypes/Content/Content.Repository.List.yaml`
- `Resources/Private/Fusion/Integration/Document/Document.Repository.fusion`
- `Resources/Private/Fusion/Integration/Document/Document.Repository.Item.fusion`
- `Resources/Private/Fusion/Integration/Content/Content.Repository.List.fusion`

## Add components from the library to your project

We have a little shell script `dev_components.sh` that helps you to add components from the library to your project.
Run `dev list-components` to get a list of all available components. Run `dev add-component` to add a
component to your project. If you already know the name of the component you want to install you can run e.g.`dev add-component Download`.

### Development

When developing the kickstarter we don't have to install the components, because the component library is included in the composer.json. 

When you want to add a new component to the library you have to:
1. Add all component files to the Library Package like you would in the Site Package
2. Add update the `./app/DistributionPackages/Sandstorm.ComponentLibrary/Configuration/Settings.Components.yaml` to add the Settings for the component when being installed.

## Custom icon font with icomoon

We use a custom icon font build with <https://icomoon.io/app>. Look at the Icons.md for more
information: `DistributionPackages/MyVendor.AwesomeNeosProject/Resources/Public/Fonts/Icons.md`

In the past we had rendering problems when we used the icon font as `<i class="icon-xyz"></i>`. To be safe we use
the `<span class="icon-xyz"></span>` syntax instead.

### Use custom icons in neos backend

Unfortunately we can't use our custom icomoon font in the neos backend directly because it just works with the
integrated fontawesome iconfont. But if we want to use our own icons anyway we can:

1. Export the SVGs of the icomoon font (e.g. in white)
2. Copy those SVGs into the `Public/SVGs` folder
3. Use the icon as SVG e.g. in the `Mixin.Icon` for the `SelectBoxEditor` by using the `resource://`
   path (<https://www.youtube.com/watch?v=Aq4w21pjriY>)

```yaml
icon-angle-down:
    label: i18n
    icon: 'resource://MyVendor.AwesomeNeosProject/SVGs/angle-down.svg'
```

## Maps

To render maps we use [Sandstorm Maps](https://maps.sandstorm.de/). To get it running you need to configure an api key.

To get an api key check <https://intern.sandstorm.de/knowledge/maps-api-sandstorm-de-nutzung> if you work at Sandstorm or
get in contact with us otherwise :)

For local development put the key in `.env`. For staging/production set the environment variable on the server, e.g. via
rancher secret.

## Menu

We currently have two menu options implemented: a "normal" menu in a navigation bar style and a mega menu. You can
choose between them during kickstart.

## Image sizes

We use Sitegeist.Kaleidoscope to define image srcsets. Make sure to define a reasonable value for `sizes` when
using `Image.fusion`. For images rendered in columns there is the `ImageSizes.fusion` helper class available, intended
to make it easier to define the `sizes` attribute for standard layouts.

## Recommended Packages

See [RECOMMENDATIONS.md](./RECOMMENDATIONS.md) for a list of recommended packages.

## Coding Guidelines

See [CODING_GUIDELINES.md](./CODING_GUIDELINES.md) for a list of coding guidelines.

[//]: # (KICKSTART_INFO_SECTION__START)

## Improving Kickstart Experience

As the script can be used to change the git remote and remove files, development becomes hard ;)

Run `./kickstart.sh --dev` to not remove certain files e.g. `./kickstart.sh`.
Run `./kickstart.sh --restore-git` after testing changes you made to `./kickstart.sh`

[//]: # (KICKSTART_INFO_SECTION__END)

## Production Setup

TODO 

### Important URLs

TODO

### Matomo

For Analytics, we usually use Matomo. Check here for tipps and tricks, e.g. on how to configure it so that we do not
need a cookie consent: https://intern.sandstorm.de/knowledge/datenschutz/matomo-zugriffsstatistiken

### Production Cookbook / Tips and Tricks

#### my Neos container does not start, how do I debug?

**Symptom:** There is an error in the Entrypoint Neos container during `docker compose up -d`.

You can start the container without executing the entrypoint with the following line:

```bash
sudo su - deploy
cd [project-name]

docker compose run --entrypoint /bin/bash neos
```

#### Connecting to the production database

When connecting to the system via SSH, use `-L` for a local port forward like this:

```bash
ssh -p29418 -L 23306:127.0.0.1:13306 [hostname]
```

Then, you can use the built-in DB browser of IntelliJ to connect:

- User `neos`
- Password: see bitwarden `TODO`
- Host: 127.0.0.1
- **Port 23306**
- Database: `neos`

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
