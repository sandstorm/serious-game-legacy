# NOTE: this file is executed from the ROOT DIRECTORY of the project, i.e. "../"

# we follow: https://frankenphp.dev/docs/docker/#how-to-install-more-caddy-modules
# BUT with pinned versions. the "crane" tool from https://github.com/google/go-containerregistry/blob/main/cmd/crane/README.md is useful
# for determining the versions, along with the following lines:
# crane ls dunglas/frankenphp | grep builder | grep 8\.3 | grep 1\.9\.1
# crane ls caddy | grep builder | grep 2.10.2
FROM dunglas/frankenphp:1.10-builder-php8.4-trixie AS builder
# Copy xcaddy in the builder image
COPY --from=caddy:2.10.2-builder /usr/bin/xcaddy /usr/bin/xcaddy

# CGO must be enabled to build FrankenPHP
# MODIFICATION: we added build module caching (--mount=...) here, + GOMODCACHE + GOCACHE declarations
RUN --mount=type=cache,target=/go/pkg/mod,sharing=locked \
    --mount=type=cache,target=/root/.cache/go-build,sharing=locked \
    CGO_ENABLED=1 \
    XCADDY_SETCAP=1 \
    XCADDY_GO_BUILD_FLAGS="-ldflags='-w -s' -tags=nobadger,nomysql,nopgx" \
    CGO_CFLAGS=$(php-config --includes) \
    CGO_LDFLAGS="$(php-config --ldflags) $(php-config --libs)" \
    GOMODCACHE=/go/pkg/mod \
    GOCACHE=/root/.cache/go-build \
    xcaddy build \
        --output /usr/local/bin/frankenphp \
        --with github.com/dunglas/frankenphp=./ \
        --with github.com/dunglas/frankenphp/caddy=./caddy/ \
        --with github.com/dunglas/caddy-cbrotli
        # Mercure and Vulcain are included in the official build, but feel free to remove them
        #--with github.com/dunglas/mercure/caddy \
        #--with github.com/dunglas/vulcain/caddy
        # Add extra Caddy modules here

################# HERE THE FRANKENPHP BUILD STOPS, and custom logic starts ##################################

FROM dunglas/frankenphp:1.10-php8.4-trixie AS php-base

# Replace the official binary by the one contained your custom modules
COPY --from=builder /usr/local/bin/frankenphp /usr/local/bin/frankenphp

# reference: https://github.com/mlocati/docker-php-extension-installer
RUN install-php-extensions \
    intl \
    bcmath \
    opcache \
    pdo \
    pdo_mysql \
    xsl \
    ffi \
    vips \
    excimer \
    redis \
    pcntl \
    zip

RUN apt-get update -y && \
    apt-get install --no-install-recommends -y  \
    unzip \
    git \
    default-mysql-client \
    inotify-tools \
    vim \
    procps \
    less \
    && apt clean \
    && rm -rf /var/lib/apt/lists/*

# run as webserver
ARG USER=www-data

RUN \
	useradd ${USER}; \
	# Give write access to /config/caddy and /data/caddy \
	chown -R ${USER}:${USER} /config/caddy /data/caddy && \
    touch /var/run/caretakerd.key && chown ${USER}:${USER} /var/run/caretakerd.key

# Add Caretaker (Startup manager)
ARG TARGETARCH
ARG CARETAKERD_VERSION=1.0.13
ARG CARETAKERD_CHECKSUM_amd64=a4aaf378b2df90dac0636b8dcd4408aaacc0eaa59b719b74edfb6433e9222cf4
ARG CARETAKERD_CHECKSUM_arm64=5f68adbb9ebe8b43fc5f7dbc5b1dbed120ce08f5571f8a85d5b59aadce98a2ef
ADD "https://github.com/echocat/caretakerd/releases/download/v${CARETAKERD_VERSION}/caretakerd-linux-${TARGETARCH}.tar.gz" caretakerd.tar.gz
RUN echo "${CARETAKERD_CHECKSUM_amd64#sha256:}  caretakerd.tar.gz" | sha256sum -c - || \
    echo "${CARETAKERD_CHECKSUM_arm64#sha256:}  caretakerd.tar.gz" | sha256sum -c - && \
    tar -xzf caretakerd.tar.gz --exclude caretakerd.html -C /usr/bin && \
    rm caretakerd.tar.gz


COPY --from=composer /usr/bin/composer /usr/local/bin/composer

ADD deployment/laravel-root/ /

# HOTFIX for ARM64 Architectures and VIPS (see https://github.com/opencv/opencv/issues/14884#issuecomment-706725583 for details)
# only needed for development on Apple Silicon Macs
RUN echo '. /etc/bash.vips-arm64-hotfix.sh' >>  /etc/bash.bashrc && \
    echo '. /etc/bash.colorprompt.sh' >>  /etc/bash.bashrc

# add colored shell env to distinguish environments properly
ENV SHELL_ENV_DISPLAY=dev

# create symlink to laravel storage
RUN ln -sf /app/storage/app/public /app/public/storage

##################################
# DEVELOPMENT Container
##################################
FROM php-base AS php-dev

RUN install-php-extensions \
    xdebug

# Install helpers
RUN apt-get update && \
    apt-get install -y procps net-tools dnsutils iputils-ping traceroute netcat-openbsd default-mysql-client && \
    apt-get clean && \
    rm -rf /var/lib/apt/lists/* /tmp/* /var/tmp/*

ADD deployment/local-dev/laravel-root/ /

# performance profiling
RUN mkdir -p /tracing/_traces/ \
    && chown -R ${USER}:${USER} /tracing

# cleanup & chown -> for DEV, the full /app dir is writable
RUN mkdir -p /app/storage && \
    chown -R ${USER} /app /var/www /app/storage

WORKDIR /app
USER ${USER}
ENTRYPOINT [ "/usr/bin/caretakerd", "run" ]


##################################
# PRODUCTION Container
##################################
FROM php-base AS php-prod

ADD ./app /app
RUN --mount=type=cache,target=/root/.composer composer install --working-dir /app/ --no-interaction --optimize-autoloader --no-dev

ENV SHELL_ENV_DISPLAY=production
ADD deployment/laravel-root/etc/bash.colorprompt.sh /etc/bash.colorprompt.sh
RUN echo '. /etc/bash.colorprompt.sh' >>  /etc/bash.bashrc

# chown for laravel storage folder ONLY
RUN mkdir -p /app/storage && \
    chown -R ${USER} /app/storage

WORKDIR /app
USER ${USER}
ENTRYPOINT [ "/usr/bin/caretakerd", "run" ]
