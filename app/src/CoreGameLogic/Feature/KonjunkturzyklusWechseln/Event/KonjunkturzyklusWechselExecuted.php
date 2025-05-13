<?php

declare(strict_types=1);

namespace Domain\CoreGameLogic\Feature\KonjunkturzyklusWechseln\Event;

use Domain\CoreGameLogic\Dto\Enum\Kompetenzbereiche;
use Domain\CoreGameLogic\Dto\Enum\KonjunkturzyklusType;
use Domain\CoreGameLogic\Dto\ValueObject\CurrentYear;
use Domain\CoreGameLogic\Dto\ValueObject\Kategorie;
use Domain\CoreGameLogic\Dto\ValueObject\Konjunkturzyklus;
use Domain\CoreGameLogic\EventStore\GameEventInterface;

final readonly class KonjunkturzyklusWechselExecuted implements GameEventInterface
{
    public function __construct(
        public CurrentYear $year,
        public Konjunkturzyklus $konjunkturzyklus,
    ) {
    }

    public static function fromArray(array $values): GameEventInterface
    {
        return new self(
            year: new CurrentYear($values['year']),
            konjunkturzyklus: new Konjunkturzyklus(
                KonjunkturzyklusType::fromString($values['konjunkturzyklus']['type']),
                $values['konjunkturzyklus']['description'],
                array_map(
                    static fn (array $category) => new Kategorie(
                        Kompetenzbereiche::fromString($category['name']),
                        $category['zeitSlots'],
                    ),
                    $values['konjunkturzyklus']['categories'],
                ),
            ),
        );
    }

    /**
     * @return array<string, mixed>
     */
    public function jsonSerialize(): array
    {
        return [
            'year' => $this->year,
            'konjunkturzyklus' => $this->konjunkturzyklus->jsonSerialize(),
        ];
    }
}
