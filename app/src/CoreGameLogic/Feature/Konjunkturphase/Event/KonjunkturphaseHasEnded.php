<?php

declare(strict_types=1);

namespace Domain\CoreGameLogic\Feature\Konjunkturphase\Event;

use Domain\CoreGameLogic\EventStore\GameEventInterface;

final readonly class KonjunkturphaseHasEnded implements GameEventInterface
{
    public function __construct(
    ) {
    }

    /**
     * @param array<string, mixed> $values
     * @return GameEventInterface
     */
    public static function fromArray(array $values): GameEventInterface
    {
        return new self(
        );
    }

    /**
     * @return array<string, mixed>
     */
    public function jsonSerialize(): array
    {
        return [
        ];
    }
}
