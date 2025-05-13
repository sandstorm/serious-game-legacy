<?php

declare(strict_types=1);

namespace Domain\CoreGameLogic\Feature\Initialization\Event;

use Domain\CoreGameLogic\Dto\ValueObject\Kategorie;
use Domain\CoreGameLogic\Dto\ValueObject\Szenario;
use Domain\CoreGameLogic\EventStore\GameEventInterface;

final readonly class JahreswechselExecuted implements GameEventInterface
{
    public function __construct(
        public string $name,
        public Szenario $szenario,
    ) {
    }

    public static function fromArray(array $values): GameEventInterface
    {
        return new self(
            $values['name'],
            szenario: new Szenario(
                $values['szenario']['value'],
                'Beschreibung',
                [
                    new Kategorie('Bildung & Karriere', 2),
                    new Kategorie('Soziales & Freizeit', 3),
                    new Kategorie('Erwerbseinkommen', 0),
                    new Kategorie('Investitionen', 4),
                ],
            ),
        );
    }

    /**
     * @return array<string, mixed>
     */
    public function jsonSerialize(): array
    {
        return [
            'name' => $this->name,
            'szenario' => $this->szenario->jsonSerialize(),
        ];
    }
}
