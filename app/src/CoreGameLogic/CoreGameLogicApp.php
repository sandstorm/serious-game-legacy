<?php

declare(strict_types=1);

namespace Domain\CoreGameLogic;

use Domain\CoreGameLogic\DrivingPorts\ForCoreGameLogic;
use Domain\CoreGameLogic\Dto\UserName;

/**
 * Main implementation of core business logic
 *
 * This class implements the driving port {@see ForCoreGameLogic} and
 * coordinates the core domain operations. It:
 * - Contains the actual business logic
 * - Uses driven ports (e.g. ForLogging) to interact with external services
 * - Never directly depends on framework code
 *
 * @internal from the outside world, you'll always use the interface {@see ForCoreGameLogic}, except when constructing this application
 */
final class CoreGameLogicApp implements ForCoreGameLogic
{
    public function __construct(
        // add driven ports here
    ) {
    }

    public function startTimeRecording(UserName $userName): void
    {
        logger()->info('starting time recording for '.$userName);
        if ($userName->value === 'sandstorm') {
            throw new \RuntimeException('no valid user name');
        }
    }
}
