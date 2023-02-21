# Sandstorm Neos on Docker Kickstart

This packages helps you to quickly set up a Neos Project. Besides a basic Neos setup
we provided examples and configuration that helps us to quickly provide a kickstart.

## requirements

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

## Setting up IntelliJ
- recommended plugins:
    - Neos Support
    - Makefile language
    - PHP
    - PHP Annotations
    - PHP Toolbox
    - Prettier
        - make sure Prettier is activated for the correct extensions
    - Docker
    - Behat Support
- check, if autocompletion works for .yaml-files
- check, if it's possible to jump to Fusion Prototypes via cmd + click

## Running Kickstart when cloning this repo

!!!If you want to clone this package for another project:!!!
Run `./kickstart.sh` an follow the instructions.

## Local Development Setup (only required once)

This should only be needed when running the project for the first time.

- run `./dev.sh setup`, this will also install the Dev Script Runner. You can now use `dev <some-taks>` from anywhere
  inside the project.
- for font awesome pro support in local dev
    - open: `app/DistributionPackages/MyVendor.AwesomeNeosProject/Resources/Private/.npmrc.sample`
    - and do what the file tells you ;)

## Local Development

- run `dev start` to start all needed container of the project (see `docker-compose.yml` for details)
- run `dev log` and `dev log-flow-exceptions` to see what's going on in the containers
- run `dev log-assets` to see the logs of scss being compiled to css and ts being compiled to js
- run `dev` to see all available commands
- run `dev <sometaks> --help` to get detailed help for a task
- run `dev open-site` you can login to the [neos backend](http://localhost:8081/neos) with the credentials `admin` and `password`

## Development

As the script can be used to change the git remote and remove files development becomes hard ;)
Run `./kickstart.sh --dev` to not remove certain files e.g. `./kickstart.sh`. 
Run `./kickstart.sh --restore-git` after testing changes you made to `./kickstart.sh`
Run `dev log` and `dev log-flow-exceptions` to see what's going on in the containers

## Staging

Staging URL is: https://myvendor-awesomeneosproject-staging.cloud.sandstorm.de/
Htaccess credantials are in bitwarden.

## IMPORT/EXPORT Content

The default flow command to import and export the site content is not stable.
So we developed an easy-to-use shell script for this case and made it part of the make file:

### EXPORT
The script places two archives in `app/ContentDump` for DB Content and the Ressources.
(Maybe init git lfs for the repo if you plan to use this script)

```bash
dev site-export
```

### IMPORT

```bash
dev site-import
```

### Backlog

* Examples for rights in Neos -> separate Distribution Package
* Custom Backend Module Beispiel in extra Distribution Package
* Frontendlogin in extra Distribution Package
* DataPrivacy -> maybe change config of Neos
* check caching config -> nginx e.g. images
* Distribution Package with search
* Check examples for accessibility issues
