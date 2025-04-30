<?php

declare(strict_types=1);

namespace Domain\CoreGameLogic\Dto\ValueObject;

use Domain\CoreGameLogic\Dto\Aktion\Aktion;

/**
 * TODO: make abstract
 */
readonly class Modifier
{
    public function __construct(public string $value)
    {
    }

    public function __toString(): string
    {
        return '[ModifierId: '.$this->value.']';
    }

    /**
     * @param mixed $applicableAktionen
     * @return Aktion[]
     */
    public function applyToAvailableAktionen(mixed $applicableAktionen): array
    {
        if ($this->value === "MODIFIER:ausetzen") {
            return [];
        }
        return $applicableAktionen;
    }
}
