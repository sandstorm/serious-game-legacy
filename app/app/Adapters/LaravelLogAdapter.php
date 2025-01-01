<?php
declare(strict_types=1);
namespace App\Adapters;

use Illuminate\Log\LogManager;
use Domain\NameOfCoreDomainX\DrivenPorts\ForLogging;

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