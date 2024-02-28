# NOTE: this file is executed from the ROOT DIRECTORY of the project, i.e. "../"
FROM php:8.1-fpm-bullseye

# ----------------
# Productive dependencies (should be equal on local-dev, staging and production):
# Install intl, bcmath, pdo, pdo_mysql, mysqli, libvips
RUN apt-get update -y && \
    apt-get install --no-install-recommends -y \
        libicu-dev \
        libxslt1-dev \
        nginx-light \
        libvips42 \
        libvips-dev \
        libffi-dev \
        supervisor \
        procps \
        libpq-dev \
        less \
        && \
    mkdir -p /var/log/supervisor && \
    rm -rf /var/lib/apt/lists/* && \
    docker-php-ext-install intl bcmath pdo pdo_mysql xsl ffi && \
    pecl install vips && \
    echo "extension=vips.so" > /usr/local/etc/php/conf.d/vips.ini && \
    pecl install redis && docker-php-ext-enable redis

# ----------------
# Development dependencies (should be equal on local-dev, staging and production):
# install git and unzip for Composer
RUN apt-get update -y && \
    apt-get install --no-install-recommends -y  \
    unzip \
    git \
    default-mysql-client \
    inotify-tools \
    vim \
    && \
    rm -rf /var/lib/apt/lists/*

# for PHP 8.1, disable deprecations to temporarily make Neos/Flow work
RUN echo "error_reporting = E_ALL & ~E_DEPRECATED & ~E_STRICT" >> /usr/local/etc/php/conf.d/error_reporting.ini

# enable FFI for VIPS in PHP config
RUN echo "ffi.enable = true" >> /usr/local/etc/php/conf.d/ffi.ini


# install composer
RUN curl --silent --show-error https://getcomposer.org/installer | php
RUN mv composer.phar /usr/local/bin/composer
RUN composer config --global cache-dir /composer_cache

# add colored shell env to distinguish environments properly
ENV SHELL_ENV_DISPLAY=staging
ADD deployment/config-files/bash.colorprompt.sh /etc/bash.colorprompt.sh
RUN echo '. /etc/bash.colorprompt.sh' >>  /etc/bash.bashrc

# Installing Supercronic
# We use this to only run a cronjob if the prvious has finished
ENV SUPERCRONIC_URL=https://github.com/aptible/supercronic/releases/download/v0.1.12/supercronic-linux-amd64 \
    SUPERCRONIC=supercronic-linux-amd64 \
    SUPERCRONIC_SHA1SUM=048b95b48b708983effb2e5c935a1ef8483d9e3e
RUN curl -fsSLO "$SUPERCRONIC_URL" \
 && echo "${SUPERCRONIC_SHA1SUM}  ${SUPERCRONIC}" | sha1sum -c - \
 && chmod +x "$SUPERCRONIC" \
 && mv "$SUPERCRONIC" "/usr/local/bin/${SUPERCRONIC}" \
 && ln -s "/usr/local/bin/${SUPERCRONIC}" /usr/local/bin/supercronic


# application entrypoint
ADD /deployment/staging/entrypoint.sh /
ADD /deployment/config-files/memory-limit-php.ini /usr/local/etc/php/conf.d/memory-limit-php.ini
ADD /deployment/config-files/upload-limit-php.ini /usr/local/etc/php/conf.d/upload-limit-php.ini

RUN rm -Rf /usr/local/etc/php-fpm.* && rm -Rf /usr/local/etc/nginx/conf.d/*
ADD /deployment/config-files/php-fpm.conf /usr/local/etc/php-fpm.conf

ADD /deployment/config-files/nginx.conf /deployment/config-files/nginx-e2etest-server-prod.conf /etc/nginx/
RUN mkdir -p /var/lib/nginx /usr/local/var/log/ & \
    chown -R www-data /var/lib/nginx /usr/local/var/log/ /etc/nginx/

# cleanup & chown
RUN mkdir -p /app/Data/Persistent /app/Configuration/Development/Docker /composer_cache && \
    chown -R www-data /app /composer_cache && \
    apt-get clean

# add the App files as www-data. For high security environments, you would try remove permissions from the app files.
ADD --chown=www-data:www-data /app/. /app/

#################### START - Staging Container only ####################
# IMPORTANT: we try to keep the production and local-dev the same      #
# all local dev related changes should stay within this block. Also    #
# consider making a diff with `production/Dockerfile` and copying over #
# the latest changes to keep both containers in sync                   #
########################################################################

ADD /deployment/staging/entrypoint-e2e.sh /

# utility scripts
ADD deployment/utilities/watchAndLogExceptions.sh /app/watchAndLogExceptions.sh
RUN chmod +x /app/watchAndLogExceptions.sh

##################### END - Staging Container only #####################

WORKDIR /app
USER www-data

ENTRYPOINT [ "/entrypoint.sh" ]
