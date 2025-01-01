<?php
declare(strict_types=1);
namespace Domain\NameOfCoreDomainX\DrivenPorts;

/**
 * Driven Port for persistence operations
 *
 *  This interface represents a "driven" port in the Ports & Adapters pattern:
 *  - The core domain USES this interface when it needs logging
 *  - The actual implementation lives in the adapters layer (e.g. LaravelLogAdapter)
 *  - This allows the core domain to remain framework-agnostic
 *
 * @api
 */
interface ForPersistence
{

}