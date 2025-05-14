<?php
declare(strict_types=1);
namespace Domain\CoreGameLogic\Feature\Initialization\State\Dto;

use Domain\CoreGameLogic\Dto\ValueObject\PlayerId;
use Domain\Definitions\Lebensziel\Model\Lebensziel;

readonly final class NameAndLebensziel
{
    public function __construct(public PlayerId $playerId, public ?string $name, public ?Lebensziel $lebensziel)
    {
    }

    public function hasNameAndLebensziel(): bool
    {
        return $this->name !== null && $this->lebensziel !== null;
    }
}
