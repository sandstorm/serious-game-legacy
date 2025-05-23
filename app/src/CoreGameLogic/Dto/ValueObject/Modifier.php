<?php

declare(strict_types=1);

namespace Domain\CoreGameLogic\Dto\ValueObject;

use Domain\CoreGameLogic\Dto\Aktion\Aktion;

/**
 * TODO: make abstract
 */
readonly class Modifier
{
    public function __construct(public ModifierId $id)
    {
    }

    public function __toString(): string
    {
        return '[ModifierId: '.$this->id.']';
    }

    /**
     * @param mixed $applicableAktionen
     * @return Aktion[]
     */
    public function applyToAvailableAktionen(mixed $applicableAktionen): array
    {
        if ($this->id->value === "MODIFIER:ausetzen") {
            return [];
        }
        return $applicableAktionen;
    }
}
