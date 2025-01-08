# NOTE: this file is executed from the ROOT DIRECTORY of the project, i.e. "../"
FROM php:8.3-fpm-bookworm

# add S6 Process Supervisor
ARG S6_OVERLAY_VERSION=3.2.0.2
# https://github.com/just-containers/s6-overlay?tab=readme-ov-file#which-architecture-to-use-depending-on-your-targetarch
ARG S6_OVERLAY_ARCH=aarch64
ADD https://github.com/just-containers/s6-overlay/releases/download/v${S6_OVERLAY_VERSION}/s6-overlay-noarch.tar.xz /tmp
RUN tar -C / -Jxpf /tmp/s6-overlay-noarch.tar.xz
ADD https://github.com/just-containers/s6-overlay/releases/download/v${S6_OVERLAY_VERSION}/s6-overlay-${S6_OVERLAY_ARCH}.tar.xz /tmp
RUN tar -C / -Jxpf /tmp/s6-overlay-aarch64.tar.xz

# Install intl, bcmath, pdo, pdo_mysql, mysqli
# debug tools: vim, inotify-tools
RUN apt-get update -y && \
    apt-get install --no-install-recommends -y \
        libicu-dev \
        libxslt1-dev \
        nginx-light \
        procps \
        libzip-dev \
        vim \
        less \
        gettext \
        && \
    mkdir -p /var/log/supervisor && \
    rm -rf /var/lib/apt/lists/* && \
    docker-php-ext-install intl bcmath opcache pdo pdo_mysql xsl zip && \
    pecl install excimer && \
    pecl install redis && docker-php-ext-enable redis excimer

# for PHP 8.1, disable deprecations to temporarily make Neos/Flow work
RUN echo "error_reporting = E_ALL & ~E_DEPRECATED & ~E_STRICT" >> /usr/local/etc/php/conf.d/error_reporting.ini

# install composer
RUN curl --silent --show-error https://getcomposer.org/installer | php
RUN mv composer.phar /usr/local/bin/composer
RUN composer config --global cache-dir /composer_cache

# add colored shell env to distinguish environments properly
ENV SHELL_ENV_DISPLAY=production
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

RUN rm -Rf /usr/local/etc/php-fpm.* && rm -Rf /etc/nginx/conf.d/*
ADD deployment/config-files/php-fpm.conf /usr/local/etc/php-fpm.conf

# performance profiling
ADD /deployment/config-files/excimer.ini /usr/local/etc/php/conf.d/excimer.ini
RUN mkdir -p /tracing/_traces/ \
    && chown -R www-data:www-data /tracing
ADD /deployment/config-files/auto_prepend_file.php /tracing/auto_prepend_file.php

# nginx config
ADD /deployment/config-files/nginx.conf /deployment/config-files/nginx-e2etest-server-prod.conf /etc/nginx/
RUN mkdir -p /var/lib/nginx /usr/local/var/log/ & \
    chown -R www-data /var/lib/nginx /usr/local/var/log/ /etc/nginx/

# cleanup & chown
RUN mkdir -p /app/storage/app /app/vendor /app/storage/logs /app/storage/framework/sessions /app/storage/framework/views /app/storage/framework/cache /composer_cache && \
    chown -R www-data /app /composer_cache /var/www && \
    apt-get clean

# add the App files as www-data. For high security environments, you would try remove permissions from the app files.
ADD --chown=www-data:www-data /app/. /app/

WORKDIR /app
USER www-data
ENTRYPOINT ["/init"]
