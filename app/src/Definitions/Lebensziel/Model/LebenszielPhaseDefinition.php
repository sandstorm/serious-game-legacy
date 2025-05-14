<?php

namespace Domain\Definitions\Lebensziel\Model;

use Domain\CoreGameLogic\Dto\Enum\KompetenzbereichEnum;

class LebenszielPhaseDefinition
{
    /**
     * @param array<KompetenzbereichEnum, LebenszielKompetenzbereichDefinition> $kompetenzen
     */
    public function __construct(
        public array $kompetenzen,
    ) {
    }

    public static function fromArray(array $values): self
    {
        $kompetenzen = [];
        foreach ($values['kompetenzen'] as $bereich => $kompetenz) {
            $kompetenzen[$bereich] = LebenszielKompetenzbereichDefinition::fromArray($kompetenz);
        }
        return new self(
            kompetenzen: $kompetenzen,
        );
    }

    public function jsonSerialize(): array
    {
        $json = [];
        foreach ($this->kompetenzen as $bereich => $kompetenz) {
            $json[$bereich] = $kompetenz->jsonSerialize();
        }
        return $json;
    }
}
