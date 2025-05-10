<?php
declare(strict_types=1);
namespace Domain\CoreGameLogic\Feature\Initialization\State\Dto;

use Domain\CoreGameLogic\Dto\ValueObject\Lebensziel;

readonly final class NameAndLebensziel
{
    public function __construct(public ?string $name, public ?Lebensziel $lebensziel)
    {
    }
}
