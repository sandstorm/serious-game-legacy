# Continuous Profiling with Excimer (wall time + CPU time)

Goal: find out where the system spends time and CPU on production, to estimate the impact on the
carbon footprint of as much of the system as possible.

Pipeline (Sandstorm standard, see "Profiling Setup Guide" in Dashica):

```
PHP (Excimer, auto_prepend_file) --HTTP/JSON--> vector agent --NATS--> aggregator --> ClickHouse --> Dashica
```

## What is profiled

`deployment/laravel-root/tracing/auto_prepend_file.php` is loaded for **every** PHP process via
`auto_prepend_file` (see `php-ini-overrides.ini`): HTTP requests (FrankenPHP) **and** CLI processes
(`artisan reverb:start`, `schedule:run`, any artisan command).

Two Excimer profilers run in parallel in each process:

| `profiling_type` | Excimer event type | measures                                                   |
|------------------|--------------------|------------------------------------------------------------|
| `wall`           | `EXCIMER_REAL`     | wall clock time, including waiting for DB, Redis, network |
| `cpu`            | `EXCIMER_CPU`      | CPU time actually consumed by the PHP thread               |

`wall - cpu` ≈ time spent waiting on I/O. For the carbon footprint, `cpu` is the relevant number.

Each payload sent to vector:

```json
{
  "traces": "<collapsed stack traces, one per line: a;b;c 3>",
  "request_path": "/livewire/update | cli: artisan reverb:start",
  "http_method": "POST | CLI",
  "profiling_type": "wall | cpu"
}
```

- Short-lived processes ship on shutdown, after the response has been sent (`frankenphp_finish_request()`),
  so users see no added latency.
- Long-running processes (Reverb) ship every `PROFILING_MAX_SAMPLES` samples via Excimer's flush callback.
- Shipping uses short cURL timeouts and never throws, so profiling cannot break the application.

## Configuration (env variables of the laravel container)

| variable                    | default | description                                                   |
|-----------------------------|---------|---------------------------------------------------------------|
| `PROFILING_VECTOR_URL`      | –       | **profiling is only active if this is set**, e.g. `http://vector:8234` |
| `PROFILING_VECTOR_USER`     | –       | basic auth user of the vector http source                     |
| `PROFILING_VECTOR_PASSWORD` | –       | basic auth password of the vector http source                 |
| `PROFILING_PERIOD`          | `1`     | sampling period in seconds                                    |
| `PROFILING_MAX_SAMPLES`     | `200`   | samples after which a log is shipped early (long-running processes) |

Local dev, CI and tests do not set `PROFILING_VECTOR_URL`, so nothing is profiled there.

## Enabling on production (docker compose)

`deployment/production/compose.yml` contains a `vector` service in the compose profile `profiling`,
and `deployment/production/vector.yaml` is copied to the server on deploy. Add to the server's `.env`:

```
COMPOSE_PROFILES=profiling
PROFILING_VECTOR_URL=http://vector:8234
VECTOR_AGENT_USERNAME=vector
VECTOR_AGENT_PASSWORD=<any random password, only used inside the compose network>
PROFILING_CUSTOMER_TENANT=<tenant>
PROFILING_CUSTOMER_PROJECT=<tenant.project>
PROFILING_HOST_NAME=<host name>
PROFILING_HOST_GROUP=<host group>
```

and place the NATS credentials file at `secrets/logging_nats_customers.creds` (next to `compose.yml`).
Then `docker compose up -d`. To disable, remove `PROFILING_VECTOR_URL` (and `COMPOSE_PROFILES`) and run `docker compose up -d` again.

## Enabling on staging (k8s)

Staging uses the shared vector agent in the `sandstorm-monitoring-v2` namespace. Create the rancher secret
`profiling-vector` in namespace `serious-game-staging` with the keys `PROFILING_VECTOR_URL`,
`PROFILING_VECTOR_USER`, `PROFILING_VECTOR_PASSWORD`, then comment in `- profiling-vector` under
`envFromSecrets` in `deployment/staging/app.yaml`.

## Interpreting the numbers

- With a 1s period most requests are shorter than one period. This is only unbiased if Excimer starts each
  profiler's timer at a random point within the period; then every sample represents ~1 period of time
  and short requests are represented proportionally to their share of the load.
  Check: ~500 requests of ~50ms should yield roughly 25 samples. If not, lower `PROFILING_PERIOD`.
- Only PHP is visible. FrankenPHP/Caddy (Go: TLS, compression, static files), MariaDB, Redis and vector itself
  are not covered; use container CPU metrics for those.

## Verified behaviour (2026-11-10, Excimer 1.2.6, FrankenPHP 1.13.2, PHP 8.4.27 ZTS)

Tested in docker against a fake vector receiver:

- **CPU time is per thread**: a 2s `usleep` request running concurrently with 4 busy requests got 200 wall and
  0 CPU samples; the busy ones 800/800.
- **Random first sample**: 500 requests of ~50ms at `PROFILING_PERIOD=1` produced ~30 samples per type
  (expected ~25), so short requests are represented proportionally.
- **Long-running CLI**: a 5s CLI process (period 10ms, max 200 samples) shipped 200 + 200 + 100 samples per type
  during the run, labelled `cli: …` / `CLI`.
- **Stability**: 24,000 requests at a 1ms period, 20 concurrent, no crash.
  NOTE: the *dev* image crashes (segfault) under the same load even with profiling off, caused by
  xdebug/pcov in threaded FrankenPHP – not by Excimer. Production does not load xdebug/pcov.
- **Overhead** at the default 1s period: ~1% throughput. Responses are not delayed even if vector is
  unreachable (shipping happens after `frankenphp_finish_request()`), but the PHP thread stays busy for up to
  2 × 200ms connect timeout for each request that has samples.
- `flush()` invokes the flush callback itself (handled in `auto_prepend_file.php`, no duplicate payloads).
- Rarely (~0.1% of requests at a 1ms period), the wall profiler records a few samples after `stop()`; they show
  up as a separate payload with stacks inside `auto_prepend_file.php` (the shipping code). Negligible at 1s.

## Open: ClickHouse / Dashica

The profiling table and aggregator need a `profiling_type` column (e.g. `LowCardinality(String) DEFAULT 'wall'`)
and the dashboard should filter on it. Without it, wall and CPU samples are mixed.
