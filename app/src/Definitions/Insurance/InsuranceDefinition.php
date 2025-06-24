<?php

declare(strict_types=1);

namespace Domain\Definitions\Insurance;

use Domain\Definitions\Card\ValueObject\MoneyAmount;
use Domain\Definitions\Insurance\ValueObject\InsuranceId;
use Domain\Definitions\Insurance\ValueObject\InsuranceTypeEnum;

class InsuranceDefinition
{
    /**
     * @param InsuranceId $id
     * @param InsuranceTypeEnum $type
     * @param string $description
     * @param MoneyAmount $annualCost
     */
    public function __construct(
        public InsuranceId       $id,
        public InsuranceTypeEnum $type,
        public string            $description,
        // TODO some insurance have different costs per phase
        public MoneyAmount       $annualCost,
        // TODO add field for benefits or coverage details
    )
    {
    }
}
