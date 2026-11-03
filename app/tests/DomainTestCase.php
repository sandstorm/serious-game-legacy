<?php

declare(strict_types=1);

namespace Tests;

use PHPUnit\Framework\TestCase as BaseTestCase;

/**
 * Base class for the tests of the domain code (app/src), which must not depend on Laravel.
 *
 * WHY: Booting the Laravel application takes ~95ms per test, which made the domain tests ~6x slower
 * and caused most mutations in mutation testing (--mutate) to time out.
 */
abstract class DomainTestCase extends BaseTestCase
{
    use GameTestHelpers;
}
