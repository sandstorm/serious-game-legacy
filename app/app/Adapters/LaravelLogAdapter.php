<?php
declare(strict_types=1);
namespace App\Adapters;

use Illuminate\Log\LogManager;
use Domain\NameOfCoreDomainX\DrivenPorts\ForLogging;

/**
 * Laravel implementation of the ForLogging port
 *
 * This adapter:
 * - Implements the domain's ForLogging port
 * - Translates between domain and Laravel logging concepts
 * - Lives in the framework layer (App namespace)
 * - Is wired up in AppServiceProvider
 *
 * @internal This is a framework-specific adapter implementation
 */
final class LaravelLogAdapter implements ForLogging
{

    public function __construct(private readonly LogManager $logManager)
    {
    }

    public function log(string $message): void
    {
        $this->logManager->log(LOG_INFO, $message);
    }
}