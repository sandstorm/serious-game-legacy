<?php

declare(strict_types=1);

namespace Domain\CoreGameLogic\Feature\Konjunkturphase\Event;

use Domain\CoreGameLogic\Dto\ValueObject\Konjunkturphase;
use Domain\CoreGameLogic\EventStore\GameEventInterface;

final readonly class KonjunkturphaseWasChanged implements GameEventInterface
{
    public function __construct(
        public Konjunkturphase $konjunkturphase,
    ) {
    }

    public static function fromArray(array $values): GameEventInterface
    {
        return new self(
            konjunkturphase: Konjunkturphase::fromArray($values['konjunkturphase']),
        );
    }

    /**
     * @return array<string, mixed>
     */
    public function jsonSerialize(): array
    {
        return [
            'konjunkturphase' => $this->konjunkturphase,
        ];
    }
}
