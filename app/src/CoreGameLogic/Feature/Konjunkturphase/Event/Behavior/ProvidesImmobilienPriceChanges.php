<?php

declare(strict_types=1);

namespace Domain\CoreGameLogic\Feature\Konjunkturphase\Event\Behavior;

use Domain\CoreGameLogic\Feature\Konjunkturphase\Dto\ImmobilienPrice;

interface ProvidesImmobilienPriceChanges
{
    /** @return ImmobilienPrice[] */
    public function getImmobilienPrices(): array;
}
