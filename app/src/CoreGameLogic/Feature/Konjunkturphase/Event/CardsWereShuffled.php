<?php

declare(strict_types=1);

namespace Domain\CoreGameLogic\Feature\Konjunkturphase\Event;

use Domain\CoreGameLogic\EventStore\GameEventInterface;
use Domain\CoreGameLogic\Feature\Konjunkturphase\Dto\CardOrdering;

final readonly class CardsWereShuffled implements GameEventInterface
{
    /**
     * @param CardOrdering[] $piles
     */
    public function __construct(
        public array $piles,
    ) {
        foreach ($this->piles as $pile) {
            assert($pile instanceof CardOrdering);
        }
    }

    public static function fromArray(array $values): GameEventInterface
    {
        return new self(
            piles: array_map(fn ($pile) => CardOrdering::fromArray($pile), $values['piles']),
        );
    }

    public function jsonSerialize(): array
    {
         return [
            'piles' => $this->piles,
         ];
    }
}
