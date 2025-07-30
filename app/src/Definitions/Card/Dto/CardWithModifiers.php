<?php

declare(strict_types=1);

namespace Domain\Definitions\Card\Dto;

use Domain\Definitions\Card\ValueObject\ModifierId;

/**
 * Use this interface for cards that may require only be added to the pile after a certain year.
 */
interface CardWithModifiers
{
    /**
     * @return ModifierId[]
     */
    public function getModifierIds(): array;
    public function getModifierParameters(): ModifierParameters;
}
