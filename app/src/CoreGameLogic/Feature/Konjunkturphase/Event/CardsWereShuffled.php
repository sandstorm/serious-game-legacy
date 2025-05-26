<?php

declare(strict_types=1);

namespace Domain\CoreGameLogic\Feature\Konjunkturphase\Event;

use Domain\CoreGameLogic\EventStore\GameEventInterface;
use Domain\CoreGameLogic\Feature\Konjunkturphase\Dto\CardOrder;

final readonly class CardsWereShuffled implements GameEventInterface
{
    /**
     * @param CardOrder[] $piles
     */
    public function __construct(
        public array $piles,
    ) {
        foreach ($this->piles as $pile) {
            assert($pile instanceof CardOrder);
        }
    }

    public static function fromArray(array $values): GameEventInterface
    {
        return new self(
            piles: array_map(fn ($pile) => CardOrder::fromArray($pile), $values['piles']),
        );
    }

    public function jsonSerialize(): array
    {
         return [
            'piles' => $this->piles,
         ];
    }
}
