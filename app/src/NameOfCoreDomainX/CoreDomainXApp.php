<?php

declare(strict_types=1);

namespace Domain\NameOfCoreDomainX;

use Domain\NameOfCoreDomainX\DrivenPorts\ForLogging;
use Domain\NameOfCoreDomainX\DrivingPorts\ForDoingCoreBusinessLogic;
use Domain\NameOfCoreDomainX\Dto\UserName;

/**
 * Main implementation of core business logic
 *
 * This class implements the driving port {@see ForDoingCoreBusinessLogic} and
 * coordinates the core domain operations. It:
 * - Contains the actual business logic
 * - Uses driven ports (e.g. ForLogging) to interact with external services
 * - Never directly depends on framework code
 *
 * @internal from the outside world, you'll always use the interface {@see ForDoingCoreBusinessLogic}, except when constructing this application
 */
final class CoreDomainXApp implements ForDoingCoreBusinessLogic
{
    public function __construct(
        private readonly ForLogging $forLogging,
    ) {
    }

    public function startTimeRecording(UserName $userName): void
    {
        $this->forLogging->log('starting time recording for '.$userName);
        if ($userName->value === 'sandstorm') {
            throw new \RuntimeException('no valid user name');
        }
    }
}
