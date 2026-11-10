<?php

// !!!!!!!!!!!!! DO NOT DELETE THIS FILE !!!!!!!!!!!!!!!!!!!!
// This file is included via auto_prepend_file in php.ini - and it is used for
// profiling.
// !!!!!!!!!!!!! DO NOT DELETE THIS FILE !!!!!!!!!!!!!!!!!!!!
//
// Continuous profiling with Excimer, shipped to the Sandstorm profiling pipeline
// (vector -> NATS -> ClickHouse -> Dashica). See docs/2026_11_10_Excimer_Profiling.md.
//
// Two profilers run in parallel for every PHP process (HTTP requests AND CLI, e.g. Reverb, scheduler):
// - "wall": EXCIMER_REAL, wall clock time (includes waiting for DB, Redis, network, ...)
// - "cpu":  EXCIMER_CPU, CPU time actually consumed by the PHP thread
//
// Profiling is ENABLED only if the env variable PROFILING_VECTOR_URL is set.
//
// Env variables:
// - PROFILING_VECTOR_URL          (required to enable) e.g. http://vector:8234
// - PROFILING_VECTOR_USER         basic auth user of the vector http source
// - PROFILING_VECTOR_PASSWORD     basic auth password of the vector http source
// - PROFILING_PERIOD              sampling period in seconds (default: 1)
// - PROFILING_MAX_SAMPLES         samples after which a log is shipped early, needed for long-running
//                                 processes like Reverb (default: 200)

(static function (): void {
    $url = getenv('PROFILING_VECTOR_URL') ?: null;
    if ($url === null) {
        // profiling disabled
        return;
    }
    if (!class_exists(\ExcimerProfiler::class)) {
        // excimer.so profiling extension not loaded.
        return;
    }

    $username = getenv('PROFILING_VECTOR_USER') ?: '';
    $password = getenv('PROFILING_VECTOR_PASSWORD') ?: '';
    $period = (float)(getenv('PROFILING_PERIOD') ?: 1);
    $maxSamples = (int)(getenv('PROFILING_MAX_SAMPLES') ?: 200);

    if (PHP_SAPI === 'cli') {
        $requestPath = 'cli: ' . implode(' ', array_map('strval', $_SERVER['argv'] ?? []));
        $httpMethod = 'CLI';
    } else {
        $requestPath = $_SERVER['REQUEST_URI'] ?? null;
        $httpMethod = $_SERVER['REQUEST_METHOD'] ?? null;
    }

    $ship = static function (string $profilingType, \ExcimerLog $log) use ($url, $username, $password, $requestPath, $httpMethod): void {
        try {
            $traces = $log->formatCollapsed();
            if ($traces === '') {
                return;
            }

            $ch = curl_init($url);
            if ($ch === false) {
                return;
            }
            curl_setopt_array($ch, [
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_POST => true,
                CURLOPT_POSTFIELDS => json_encode([
                    'traces' => $traces,
                    // set to null so default of database is used
                    'request_path' => $requestPath,
                    'http_method' => $httpMethod,
                    'profiling_type' => $profilingType,
                ]),
                CURLOPT_HTTPHEADER => ['Content-Type: application/json'],
                CURLOPT_USERPWD => "$username:$password",
                // never let profiling block the application for long
                CURLOPT_CONNECTTIMEOUT_MS => 200,
                CURLOPT_TIMEOUT_MS => 1000,
            ]);
            curl_exec($ch);
            curl_close($ch);
        } catch (\Throwable) {
            // profiling must never break the application
        }
    };

    $profilers = [];
    // per profiling type: did the flush callback ship the log? (see shutdown below)
    $shippedByCallback = [];
    foreach (['wall' => EXCIMER_REAL, 'cpu' => EXCIMER_CPU] as $profilingType => $eventType) {
        $profiler = new \ExcimerProfiler();
        $profiler->setPeriod($period);
        $profiler->setEventType($eventType);
        // ship early for long-running processes (e.g. artisan reverb:start), otherwise we only get data on shutdown
        $profiler->setFlushCallback(
            static function (\ExcimerLog $log) use ($ship, $profilingType, &$shippedByCallback): void {
                $shippedByCallback[$profilingType] = true;
                $ship($profilingType, $log);
            },
            $maxSamples,
        );
        $profiler->start();
        $profilers[$profilingType] = $profiler;
    }

    $shutdown = static function () use ($profilers, $ship, &$shippedByCallback): void {
        foreach ($profilers as $profiler) {
            $profiler->stop();
        }

        // send the response to the client first, so shipping the profiles does not add latency
        if (PHP_SAPI !== 'cli' && function_exists('frankenphp_finish_request')) {
            frankenphp_finish_request();
        }

        foreach ($profilers as $profilingType => $profiler) {
            // WHY: Excimer (verified with 1.2.6) invokes the flush callback from flush() itself AND returns the
            // same log - shipping the return value too would send every log twice. We only ship it ourselves
            // if the callback did not run (e.g. other Excimer versions).
            $shippedByCallback[$profilingType] = false;
            $log = $profiler->flush();
            if (!$shippedByCallback[$profilingType]) {
                $ship($profilingType, $log);
            }
        }
    };

    // WHY nested: this file runs before the application, so a plain registration would run BEFORE Laravel's
    // shutdown handlers (e.g. rendering fatal errors). Registering from within a shutdown function appends
    // to the end of the queue, so we profile everything and finish the request last.
    register_shutdown_function(static function () use ($shutdown): void {
        register_shutdown_function($shutdown);
    });
})();
