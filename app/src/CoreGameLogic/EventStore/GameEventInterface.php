<?php

declare(strict_types=1);

namespace Domain\CoreGameLogic\EventStore;

/**
 * Common interface for all Game "domain events"
 *
 * @api
 */
interface GameEventInterface extends \JsonSerializable
{
    /**
     * @param array<string,mixed> $values
     */
    public static function fromArray(array $values): GameEventInterface;

    /**
     * @return array<string,mixed>
     */
    public function jsonSerialize(): array;
}
