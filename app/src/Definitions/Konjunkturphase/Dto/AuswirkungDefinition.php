<?php

declare(strict_types=1);

namespace Domain\Definitions\Konjunkturphase\Dto;

use Domain\Definitions\Konjunkturphase\ValueObject\AuswirkungScopeEnum;

/**
 * represents the model of the Auswirkung used by the repository to fill the game with data
 */
class AuswirkungDefinition
{
    /**
     * @param AuswirkungScopeEnum $scope
     * @param float $modifier
     */
    public function __construct(
        public AuswirkungScopeEnum $scope,
        public float $modifier,
    ) {
    }
}
