# Sandstorm Neos on Docker Kickstart

[//]: # (KICKSTART_INFO_SECTION__START)

This packages helps you to quickly set up a Neos Project. Besides a basic Neos setup
we provided examples and configuration that helps us to quickly provide a kickstart.

Run `./kickstart.sh` and follow the instructions.

[//]: # (KICKSTART_INFO_SECTION__END)

<!-- TOC -->
* [Sandstorm Neos on Docker Kickstart](#sandstorm-neos-on-docker-kickstart)
  * [Requirements](#requirements)
  * [Features](#features)
  * [Initial Setup (required once)](#initial-setup--required-once-)
    * [Install Dependencies](#install-dependencies)
    * [Setting up IntelliJ](#setting-up-intellij)
  * [Local Development](#local-development)
  * [Testing](#testing)
    * [E2E Test](#e2e-test)
      * [Debug Failing Tests](#debug-failing-tests)
      * [Run Single BDD Feature Files / Scenarios](#run-single-bdd-feature-files--scenarios)
      * [Generating content (node) fixtures workflow](#generating-content--node--fixtures-workflow)
    * [Accessibility Tests](#accessibility-tests)
      * [Testing without htaccess](#testing-without-htaccess-eg-production)
      * [Testing with htaccess](#testing-with-htaccess-eg-staging)
      * [Results](#results) 
  * [Staging](#staging)
  * [Site Export / Site Import](#site-export--site-import)
  * [Automatic Translation with DeepL](#automatic-translation-with-deepl)
  * [Kickstart repository nodetypes](#kickstart-repository-nodetypes)
  * [Custom icon font with icomoon](#custom-icon-font-with-icomoon)
  * [Maps](#maps)
  * [Improving Kickstart Experience](#improving-kickstart-experience)
  * [Backlog](#backlog)
<!-- TOC -->

## Requirements

- docker for mac
  - enable VirtioFS in docker host settings (experimental features)
  - alternatively, comment out the volume mount in the docker-compose.yml if you encounter bad local performance 
- node -> to run Playwright Tests or for local development (without docker) of your sites JavaScript

## Features

- Neos 8.2
- PHP 8.1
- MariaDB 10.3
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
- run `./dev.sh setup` to install the Dev Script Runner. You can now use `dev <some-taks>` from anywhere
  inside the project.

### Setting up IntelliJ

- recommended plugins:
  - Neos Support
  - PHP
  - PHP Annotations
  - PHP Toolbox
  - Prettier
    - make sure Prettier is activated for the correct extensions
  - Docker
  - Behat Support
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

## Testing

run `dev run-unit-tests` to run all unit tests

run `dev run-functional-tests` to run all functional tests

run `start-e2e-testrunner` and in new console `dev run-e2e-tests` to run all e2e tests

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

### Accessibility Tests

We have CI tasks for staging `a11y_test_staging` and production `a11y_test_production` which will create us an a11y test report as an job artifact.

The test uses pa11y, so have a look at https://github.com/pa11y/pa11y-ci for possible configuration options.

#### Testing without htaccess (e.g. production)
If we don't have an htaccess in front of the page, we can use the `.pa11yci` file to define all urls of the page we want to test. Just rename `.pa11yci.sample` to `.pa11yci` and add the urls you want to be tested. We also can make screen captures if we want to (see `.pa11yci.sample`). When we use it this way, we don't have to use the `--config` flag like we do in the `a11y_test_staging` job because it will pick up the `.pa11yci` config automatically.

#### Testing with htaccess (e.g. staging)
If we have an htaccess in front of the page we can write the pa11y config like we would do with the `.pa11yci` file but store it in a gitlab variable. It's importent that the variable is of type `file` (Settings > CI/CD > Variables). For the kickstarter we use the variable `$A11Y_TEST`. If we want to add an url we want to test, we can just edit this variable. If you cannot see the Settings just ask another Sandstormee with access rights ;) 

#### Results
In both cases htaccess or not the results will be stored as job artifacts and can be downloaded. The html-report can be found in the directory `pa11y-ci-report` and if we decided to get some screenshots, they will be stored in the directory `pa11y-ci-report-images`.


## Staging

run `dev open-staging` to open the staging url in the browser.

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

Otherwise you may get an error that the pods could not be found.
If you're not working at sandstorm update this command for your environment. :)

## Automatic Translation with DeepL

We use Sitegeist.LostInTranslation to translate documents and content automatically once editors choose to "create and
copy" a version in another language. See
the [package repository](https://github.com/sitegeist/Sitegeist.LostInTranslation) to check what you need to configure.

DeepL is used for the translation, therefore you need to have an api key for it. For local development put the key
in `.env`. For staging/production set the environment variable on the server, e.g. via rancher secret.

## Kickstart repository nodetypes

We found that to create repository nodetypes with a parent, several children and some kind of aggregation ("show me all
child nodes as teasers") you would have to do some repetitive tasks like creating the yaml files for all node types,
write the fusion aggregation logic, write some basic presentational code, consider caching, and so on. You can
use `dev generate-repository` to have all this work be done for you :)

The command will create the following files:

* NodeTypes/Document/Document.Repository.yaml
* NodeTypes/Document/Document.Repository.Item.yaml
* NodeTypes/Content/Content.Repository.List.yaml
* Resources/Private/Fusion/Integration/Document/Document.Repository.fusion
* Resources/Private/Fusion/Integration/Document/Document.Repository.Item.fusion
* Resources/Private/Fusion/Integration/Content/Content.Repository.List.fusion

## Custom icon font with icomoon

We use a custom icon font build with https://icomoon.io/app.
Look at the Icons.md for more information: `DistributionPackages/MyVendor.AwesomeNeosProject/Resources/Public/Fonts/Icons.md`

## Maps

To render maps we use [Sandstorm Maps](https://maps.sandstorm.de/). To get it running you need to configure an api key.

To get an api key check https://intern.sandstorm.de/knowledge/maps-api-sandstorm-de-nutzung if you work at Sandstorm or
get in contact with us otherwise :)

For local development put the key in `.env`. For staging/production set the environment variable on the server, e.g. via
rancher secret.

[//]: # (KICKSTART_INFO_SECTION__START)

## Improving Kickstart Experience

As the script can be used to change the git remote and remove files, development becomes hard ;)

Run `./kickstart.sh --dev` to not remove certain files e.g. `./kickstart.sh`.
Run `./kickstart.sh --restore-git` after testing changes you made to `./kickstart.sh`

[//]: # (KICKSTART_INFO_SECTION__END)

## Backlog

* Examples for rights in Neos -> separate Distribution Package
* Custom Backend Module Beispiel in extra Distribution Package
* Frontendlogin in extra Distribution Package
* DataPrivacy -> maybe change config of Neos
* check caching config -> nginx e.g. images
* Distribution Package with search
* Check examples for accessibility issues
