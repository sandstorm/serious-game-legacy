<?php

declare(strict_types=1);

namespace Domain\Definitions\Konjunkturphase\Dto;

use Domain\Definitions\Konjunkturphase\ValueObject\CategoryId;

/**
 * represents the model of the Kompetenzbereich used by the repository to fill the game with data
 */
class KompetenzbereichDefinition implements \JsonSerializable
{
    /**
     * @param CategoryId $name
     * @param Zeitslots $zeitslots
     */
    public function __construct(
        public CategoryId $name,
        public Zeitslots  $zeitslots,
    )
    {
    }

    /**
     * @param array<string,mixed> $in
     */
    public static function fromArray(array $in): self
    {
        return new self(
            name: CategoryId::from($in['name']),
            zeitslots: Zeitslots::fromArray($in['zeitslots']),
        );
    }

    public function __toString(): string
    {
        return '[Kompetenzbereich: ' . $this->name->value . ']';
    }

    /**
     * @return array<string, mixed>
     */
    public function jsonSerialize(): array
    {
        return [
            'name' => $this->name->value,
            'zeitslots' => $this->zeitslots,
        ];
    }
}
