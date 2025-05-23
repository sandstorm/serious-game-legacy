<?php

declare(strict_types=1);

namespace Domain\CoreGameLogic\Feature\KonjunkturzyklusWechseln\Event;

use Domain\CoreGameLogic\Dto\ValueObject\Konjunkturzyklus;
use Domain\CoreGameLogic\EventStore\GameEventInterface;

final readonly class KonjunkturzyklusWechselExecuted implements GameEventInterface
{
    public function __construct(
        public Konjunkturzyklus $konjunkturzyklus,
    ) {
    }

    public static function fromArray(array $values): GameEventInterface
    {
        return new self(
            konjunkturzyklus: Konjunkturzyklus::fromArray($values['konjunkturzyklus']),
        );
    }

    /**
     * @return array<string, mixed>
     */
    public function jsonSerialize(): array
    {
        return [
            'konjunkturzyklus' => $this->konjunkturzyklus,
        ];
    }
}
