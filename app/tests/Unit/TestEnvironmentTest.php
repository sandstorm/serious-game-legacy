<?php

declare(strict_types=1);

// The docker container and the CI set CACHE_STORE=redis. Tests must not share state (e.g. rate limiter counters)
// via redis - neither between tests nor between test runs.
it('uses the array cache store in tests, regardless of the environment', function () {
    expect(config('cache.default'))->toBe('array');
});
