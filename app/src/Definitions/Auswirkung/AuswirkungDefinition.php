<?php

declare(strict_types=1);

namespace Domain\Definitions\Auswirkung;

use Domain\Definitions\Auswirkung\Enum\AuswirkungScopeEnum;

/**
 * represents the model of the Auswirkung used by the repository to fill the game with data
 */
class AuswirkungDefinition
{
    public function __construct(
        public AuswirkungScopeEnum $scope,
        public string $modifier,
    ) {
    }
}
