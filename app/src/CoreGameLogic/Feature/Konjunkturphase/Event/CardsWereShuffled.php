<?php

declare(strict_types=1);

namespace Domain\CoreGameLogic\Feature\Konjunkturphase\Event;

use Domain\CoreGameLogic\EventStore\GameEventInterface;
use Domain\CoreGameLogic\Feature\Konjunkturphase\Dto\Pile;

final readonly class CardsWereShuffled implements GameEventInterface
{
    /**
     * @param Pile[] $piles
     */
    public function __construct(
        public array $piles,
    ) {
        foreach ($this->piles as $pile) {
            assert($pile instanceof Pile);
        }
    }

    public static function fromArray(array $values): GameEventInterface
    {
        return new self(
            piles: array_map(fn ($pile) => Pile::fromArray($pile), $values['piles']),
        );
    }

    public function jsonSerialize(): array
    {
         return [
            'piles' => $this->piles,
         ];
    }
}
