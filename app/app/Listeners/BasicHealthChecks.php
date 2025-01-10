<?php

declare(strict_types=1);

namespace App\Listeners;

use Illuminate\Contracts\Redis\Connection as RedisConnection;
use Illuminate\Database\ConnectionInterface;
use Illuminate\Foundation\Events\DiagnosingHealth;

final class BasicHealthChecks
{
    /**
     * Create the event listener.
     */
    public function __construct(
        private readonly ConnectionInterface $dbConnection,
        private readonly RedisConnection $redisConnection
    ) {
    }

    /**
     * Handle the event.
     */
    public function handle(DiagnosingHealth $event): void
    {
        // Throws if connection failed
        $this->dbConnection->selectOne('SELECT 1');

        // Throws if connection failed
        $this->redisConnection->command('PING');
    }
}
