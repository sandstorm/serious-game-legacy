<?php
declare(strict_types=1);
namespace Domain\NameOfCoreDomainX\DrivingPorts;

use Domain\NameOfCoreDomainX\Dto\UserName;

/**
 * Driving Port for core business operations
 *
 * This is the primary entry point into the domain from external code:
 * - External code (e.g. controllers) depend on this interface
 * - The implementation {@see CoreDomainXApp} contains the actual logic
 * - Methods define the allowed operations on this domain
 *
 * Because this is wired in AppServiceProvider, you can *inject this everywhere*
 * in Laravel.
 *
 * Example usage from a Laravel controller:
 *
 * ```php
 * class TimeController {
 *     public function __construct(
 *         private readonly ForDoingCoreBusinessLogic $core
 *     ) {}
 * }
 * ```
 *
 * TODO: RENAME to the core of the business logic you want to model.
 *
 * @api Main entry point into the core domain
 */
interface ForDoingCoreBusinessLogic
{
    public function startTimeRecording(UserName $userName): void;
}