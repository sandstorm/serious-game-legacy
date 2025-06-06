# NOTE: this file is executed from the ROOT DIRECTORY of the project, i.e. "../"
FROM php:8.3-fpm-bookworm

###################### START - Dev Container only ######################
# IMPORTANT: we try to keep the production and local-dev the same      #
# all local dev related changes should stay within this block. Also    #
# consider making a diff with `production/Dockerfile` and copying over #
# the latest changes to keep both containers in sync                   #
########################################################################

# install git and unzip for Composer
RUN apt-get update -y && \
    apt-get install --no-install-recommends -y  \
    unzip \
    git \
    default-mysql-client \
    inotify-tools \
    fswatch \
    vim \
    && \
    rm -rf /var/lib/apt/lists/*

# HOTFIX for ARM64 Architectures and VIPS (see https://github.com/opencv/opencv/issues/14884#issuecomment-706725583 for details)
ADD deployment/config-files/bash.vips-arm64-hotfix.sh /etc/bash.vips-arm64-hotfix.sh
RUN echo '. /etc/bash.vips-arm64-hotfix.sh' >>  /etc/bash.bashrc

####################### END - Dev Container only ########################

# add S6 Process Supervisor
ARG S6_OVERLAY_VERSION=3.2.0.2
# https://github.com/just-containers/s6-overlay?tab=readme-ov-file#which-architecture-to-use-depending-on-your-targetarch
ARG S6_OVERLAY_ARCH=aarch64
ADD https://github.com/just-containers/s6-overlay/releases/download/v${S6_OVERLAY_VERSION}/s6-overlay-noarch.tar.xz /tmp
RUN tar -C / -Jxpf /tmp/s6-overlay-noarch.tar.xz
ADD https://github.com/just-containers/s6-overlay/releases/download/v${S6_OVERLAY_VERSION}/s6-overlay-${S6_OVERLAY_ARCH}.tar.xz /tmp
RUN tar -C / -Jxpf /tmp/s6-overlay-${S6_OVERLAY_ARCH}.tar.xz

# Install intl, bcmath, pdo, pdo_mysql, mysqli
# debug tools: vim, inotify-tools
RUN apt-get update -y && \
    apt-get install --no-install-recommends -y \
        libicu-dev \
        libxslt1-dev \
        libuv1-dev \
        nginx-light \
        procps \
        libzip-dev \
        vim \
        less \
        gettext \
        && \
    mkdir -p /var/log/supervisor && \
    rm -rf /var/lib/apt/lists/* && \
    docker-php-ext-install intl bcmath opcache pdo pdo_mysql xsl zip pcntl && \
    pecl install excimer && \
    pecl install xdebug && \
    pecl install redis && docker-php-ext-enable redis excimer && \
    pecl install channel://pecl.php.net/uv-0.3.0 && docker-php-ext-enable uv &&\
    docker-php-ext-enable xdebug

# for PHP 8.1, disable deprecations to temporarily make Neos/Flow work
RUN echo "error_reporting = E_ALL & ~E_DEPRECATED & ~E_STRICT" >> /usr/local/etc/php/conf.d/error_reporting.ini

# install composer
# IMPORTANT:
#   do not call the composer command inside the Dockerfile!
#   Otherwise, the config files will be created in root home and are not
#   readable by www-data later. (unless you chown it explicitly)
RUN curl --silent --show-error https://getcomposer.org/installer | php && \
    mv composer.phar /usr/local/bin/composer

# add colored shell env to distinguish environments properly
ENV SHELL_ENV_DISPLAY=dev
ADD deployment/config-files/bash.colorprompt.sh /etc/bash.colorprompt.sh
RUN echo '. /etc/bash.colorprompt.sh' >>  /etc/bash.bashrc

# Installing Supercronic
# We use this to only run a cronjob if the prvious has finished
ENV SUPERCRONIC_URL=https://github.com/aptible/supercronic/releases/download/v0.2.33/supercronic-linux-amd64 \
    SUPERCRONIC_SHA1SUM=71b0d58cc53f6bd72cf2f293e09e294b79c666d8 \
    SUPERCRONIC=supercronic-linux-amd64
RUN curl -fsSLO "$SUPERCRONIC_URL" \
 && echo "${SUPERCRONIC_SHA1SUM}  ${SUPERCRONIC}" | sha1sum -c - \
 && chmod +x "$SUPERCRONIC" \
 && mv "$SUPERCRONIC" "/usr/local/bin/${SUPERCRONIC}" \
 && ln -s "/usr/local/bin/${SUPERCRONIC}" /usr/local/bin/supercronic

# Crontab
ADD /deployment/config-files/crontab /crontab

# application entrypoint
ADD /deployment/config-files/s6-rc.d/ /etc/s6-overlay/s6-rc.d/
ADD /deployment/config-files/memory-limit-php.ini /usr/local/etc/php/conf.d/memory-limit-php.ini
ADD /deployment/config-files/upload-limit-php.ini /usr/local/etc/php/conf.d/upload-limit-php.ini
ADD /deployment/config-files/docker-php-ext-xdebug.ini /usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini

RUN rm -Rf /usr/local/etc/php-fpm.* && rm -Rf /etc/nginx/conf.d/*
ADD deployment/config-files/php-fpm.conf /usr/local/etc/php-fpm.conf

# performance profiling
ADD /deployment/config-files/excimer.ini /usr/local/etc/php/conf.d/excimer.ini
RUN mkdir -p /tracing/_traces/ \
    && chown -R www-data:www-data /tracing
ADD /deployment/config-files/auto_prepend_file.php /tracing/auto_prepend_file.php

# nginx config
ADD /deployment/config-files/nginx.conf /etc/nginx/
RUN mkdir -p /var/lib/nginx /usr/local/var/log/ && \
    chown -R www-data /var/lib/nginx /usr/local/var/log/ /etc/nginx/

# cleanup & chown
RUN mkdir -p /app/storage/app /app/vendor /app/storage/logs /app/storage/framework/sessions /app/storage/framework/views /app/storage/framework/cache /composer_cache && \
    chown -R www-data /app /composer_cache /var/www && \
    apt-get clean

WORKDIR /app

###################### START - Dev Container only ######################
# IMPORTANT: we try to keep the production and local-dev the same      #
# all local dev related changes should stay within this block. Also    #
# consider making a diff with `production/Dockerfile` and copying over #
# the latest changes to keep both containers in sync                   #
########################################################################
RUN touch /etc/s6-overlay/s6-rc.d/user/contents.d/oneshot-artisan-db-seed

# utility scripts
ADD deployment/utilities/watchAndLogExceptions.sh /app/watchAndLogExceptions.sh
RUN chmod +x /app/watchAndLogExceptions.sh

####################### END - Dev Container only ########################

USER www-data
ENTRYPOINT ["/init"]
