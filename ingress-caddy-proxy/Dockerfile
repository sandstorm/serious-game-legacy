FROM caddy:2.8.1-builder AS builder

RUN xcaddy build
    # Add your caddy addons here AND add a Backslash (\) at the end of the line ABOVE: "RUN xcaddy build \"
    # --with github.com/sandstorm/caddy-nats-bridge@v0.7.0

FROM caddy:2.8.1

COPY --from=builder /usr/bin/caddy /usr/bin/caddy
