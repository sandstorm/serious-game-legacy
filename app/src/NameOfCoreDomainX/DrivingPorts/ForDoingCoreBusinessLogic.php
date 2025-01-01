<?php
declare(strict_types=1);
namespace Domain\NameOfCoreDomainX\DrivingPorts;

use Domain\NameOfCoreDomainX\Dto\UserName;

/**
 * This is the DRIVING side of the Ports&Adapters pattern for this
 * core domain - so always when somebody else wants to trigger the
 * core domain to do something.
 *
 * Examples:
 * - TODO
 *
 * Because this is wired in AppServiceProvider, you can *everywhere*
 * in the Laravel world inject an instance of this interface to interact
 * with the core domain.
 *
 * @api main entry point into the core domain from the outside world.
 * TODO: RENAME to the core of the business logic you want to model.
 */
interface ForDoingCoreBusinessLogic
{
    public function startTimeRecording(UserName $userName): void;
}