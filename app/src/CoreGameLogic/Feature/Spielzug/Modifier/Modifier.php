<?php

declare(strict_types=1);

namespace Domain\CoreGameLogic\Feature\Spielzug\Modifier;

use Domain\CoreGameLogic\Feature\Spielzug\Aktion\Aktion;
use Domain\CoreGameLogic\Feature\Spielzug\ValueObject\ModifierId;

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
        return '[ModifierId: '.$this->id->value.']';
    }

    /**
     * @param mixed $applicableAktionen
     * @return Aktion[]
     * @deprecated this needs to be refactored
     */
    public function applyToAvailableAktionen(mixed $applicableAktionen): array
    {
        if ($this->id === ModifierId::AUSSETZEN) {
            return [];
        }
        return $applicableAktionen;
    }

    public function applyToAvailableZeitsteine(int $currentlyAvailableZeitsteine): int
    {
        if ($this->id === ModifierId::BIND_ZEITSTEIN) {
            return max([$currentlyAvailableZeitsteine - 1, 0]);
        }
        return $currentlyAvailableZeitsteine;
    }
}
