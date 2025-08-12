<?php

declare(strict_types=1);

namespace Domain\Definitions\Konjunkturphase\Dto;

use Domain\Definitions\Card\Dto\ResourceChanges;
use Domain\Definitions\Card\ValueObject\EreignisPrerequisitesId;

/**
 * represents the model of the ResourceChanges that may occur when the player meets a certain requirement.
 */
class ConditionalResourceChange
{
    /**
     * @param EreignisPrerequisitesId $prerequisite
     * @param ResourceChanges $resourceChanges
     */
    public function __construct(
        public EreignisPrerequisitesId $prerequisite,
        public ResourceChanges $resourceChanges,
    ) {
    }
}
