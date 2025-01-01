<?php
declare(strict_types=1);
namespace Domain\NameOfCoreDomainX\DrivenPorts\Mocks;

use Domain\NameOfCoreDomainX\DrivenPorts\ForLogging;

/**
 * @internal usually needed for unit testing, but sometimes also helpful during specific normal operation (i.e. to fake certain parts of the system)
 */
class MockLogger implements ForLogging
{
    public function log(string $message): void
    {
    }
}