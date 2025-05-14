<?php

namespace Domain\Definitions\Lebensziel\Model;

use Domain\CoreGameLogic\Dto\Enum\KompetenzbereichEnum;

class LebenszielPhase
{
    /**
     * @param array<KompetenzbereichEnum, LebenszielKompetenzbereich> $kompetenzen
     */
    public function __construct(
        public array $kompetenzen,
    ) {
    }

    public static function fromArray(array $values): self
    {
        $kompetenzen = [];
        foreach ($values['kompetenzen'] as $bereich => $kompetenz) {
            $kompetenzen[$bereich] = LebenszielKompetenzbereich::fromArray($kompetenz);
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
